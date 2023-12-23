
var temp_ba_file = null;
var temp_ba_extension = null;

var temp_nonbudget_olditem_file = null;
var temp_nonbudget_olditem_extension = null;

var temp_olditem_file = null;
var temp_olditem_extension = null;

$(document).ready(function () {
    // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
    tableVendorRefreshed();
    $('.requirement_date').val(moment().add(14,'days').format('YYYY-MM-DD'));
    $('.requirement_date').prop('min',moment().add(14,'days').format('YYYY-MM-DD'));
    $('.requirement_date').trigger('change');

    $('.salespoint_select2').on('change', function () {
        let salespoint_select = $('.salespoint_select2');
        let area_status = $('.area_status');
        let authorization_select = $('.authorization_select2');
        let loading = $('.loading_salespoint_select2');

        let isjawasumatra = salespoint_select.find('option:selected').data('isjawasumatra');
        let region = salespoint_select.find('option:selected').data('region');

        // initial state
        if (isjawasumatra == 1) {
            area_status.text('Dalam Jawa Sumatra');
        } else if (isjawasumatra == 0) {
            area_status.text('Luar Jawa Sumatra');
        } else {
            area_status.text('-');
        }
        authorization_select.prop('disabled', true);
        authorization_select.find('option').remove();
        var empty = new Option('-- Pilih Otorisasi --', "", false, true);
        authorization_select.append(empty);
        authorization_select.trigger('change');

        if (salespoint_select.val() == "") {
            return;
        }

        $('.division_field').addClass('d-none');
        $('.indirect_salespoint_field').addClass('d-none');
        if(region == 17){
            // jika region HO munculkan pilih division field
            $('.division_field').removeClass('d-none');
            $('.indirect_salespoint_field').removeClass('d-none');
        }

        loading.show();
        $.ajax({
            type: "get",
            url: "/getsalespointauthorization/" + salespoint_select.val(),
            success: function (response) {
                let data = response.data;
                data.forEach(item => {
                    let option_text = "";
                    let names = [];
                    item.detail.forEach(detail=>{
                        names.push(detail.name);
                    });
                    option_text += names.join(" -- ");
                    if(item.notes){
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

    $('.item_type').on('change', function () {
        let request_select = $('.request_type');
        let type = $(this).val();
        // 0 barang
        // 1 jasa
        request_select.val("");
        request_select.trigger('change');
        request_select.prop('disabled',false);
        switch (type) {
            case "0":
                break;
            case "1":
                break;
            case "2":
                // maintenance (Jenis Pengadaan set ke baru, jenis budget ke budget munculin list item maintenance)
                $('.request_type').val(0);
                $('.request_type').prop('disabled',true);
                $('.request_type').trigger('change');
                $('.budget_type').val(0);
                $('.budget_type').prop('disabled',true);
                $('.budget_type').trigger('change');
                break;
            case "3":
                break;
            default:
                // not selected
                request_select.prop('disabled',true);
                break;
        }

    });

    $('.request_type').on('change', function () {
        // cek kalo maintenance = do nothin ke budget select
        let is_it = $('.is_it');
        is_it.prop('disabled',true);
        is_it.val('');
        if($(this).val()!=""){
            is_it.prop('disabled',false);
        }
        is_it.trigger('change');
        if($('.item_type').val() == 2){
            return;
        }

        if($(this).val()==1){
            $('.budget_olditem_field').removeClass('d-none');
            $('.nonbudget_olditem_field').removeClass('d-none');
        }else{
            $('.budget_olditem_field').addClass('d-none');
            $('.nonbudget_olditem_field').addClass('d-none');
        }
    });

    $('.is_it').on('change', function(){
        let budget_type = $('.budget_type');
        if($('.item_type').val() == 2){
        }else{
            budget_type.prop('disabled',true);
            budget_type.val('');
            if($(this).val()!=""){
                budget_type.prop('disabled',false);
            }
        }
        budget_type.trigger('change');
    })

    $('.budget_type').on('change', function () {
        let type = $(this).val();

        $('.budget_item_adder').addClass('d-none');
        $('.nonbudget_item_adder').addClass('d-none');
        $('.maintenance_item_adder').addClass('d-none');
        $('.indirect_salespoint_field').addClass('d-none');
        if (type === '0') {
            // budget
            if($('.item_type').val() == 0 || $('.item_type').val() == 1){
                // Barang / Jasa
                $('.budget_item_adder').removeClass('d-none');
                $('.budget_item_adder').find('input textarea').val('');
                $('.budget_item_adder').find('select').val("");
                $('.budget_item_adder').find('select').trigger('change');
            }else if($('.item_type').val() == 2){
                $('.maintenance_item_adder').removeClass('d-none');
                $('.maintenance_item_adder').find('input').val('');
                $('.maintenance_item_adder').find('select').val("");
                $('.maintenance_item_adder').find('select').trigger('change');
            }else{
                $('.ho_item_adder').removeClass('d-none');
                $('.ho_item_adder').find('input').val('');
                $('.ho_item_adder').find('select').val("");
                $('.ho_item_adder').find('select').trigger('change');
            }

        } else if (type === '1') {
            // non budget
            $('.nonbudget_item_adder').removeClass('d-none');
            $('.budget_item_adder').find('input textarea').val('');
        }else{

        }

        // filter item berdasarkan Jenis Item dan Jenis IT
        let is_it = $(".is_it").val();
        if($('.item_type').val() == 0){
            // Barang
            $('.select_budget_item').find('option').each((index,el)=>{
                if($(el).data('categorycode')!="JS" && $(el).data('is_it') == is_it){
                    $(el).prop('disabled',false);
                }else{
                    $(el).prop('disabled',true);
                }
            });
        }else if($('.item_type').val() == 1){
            // Jasa
            $('.select_budget_item').find('option').each((index,el)=>{
                if($(el).data('categorycode')=="JS" && $(el).data('is_it') == is_it){
                    $(el).prop('disabled',false);
                }else{
                    $(el).prop('disabled',true);
                }
            });
        }else if($('.item_type').val() == 2){
            // Maintenance
            $('#maintenance_item_select').find('option').each((index,el)=>{
                if($(el).data('is_it') == is_it){
                    $(el).prop('disabled',false);
                }else{
                    $(el).prop('disabled',true);
                }
            });
        }else if($('.item_type').val() == 3){
            // HO
            $('#ho_item_select').find('option').each((index,el)=>{
                if($(el).data('is_it') == is_it){
                    $(el).prop('disabled',false);
                }else{
                    $(el).prop('disabled',true);
                }
            });
        }else{}
    });

    $('.division_select').change(function() {
        if($(this).val() === "Indirect"){
            $('.indirect_salespoint').prop('disabled',false);
        }else{
            $('.indirect_salespoint').prop('disabled',true);
        }
        $('.indirect_salespoint').val("");
        $('.indirect_salespoint').trigger("change");
    });

    $('.select_budget_item').on('change', function () {
        let salespoint_select = $('.salespoint_select2');
        let isjawasumatra = salespoint_select.find('option:selected').data('isjawasumatra');
        let item_min_price = $('.item_min_price');
        let item_max_price = $('.item_max_price');
        let price = $('.price_budget_item');
        let price_field = autoNumeric_field[$('.rupiah').index(price)];
        price_field.set(0);

        let minjs = $(this).find('option:selected').data('minjs');
        let maxjs = $(this).find('option:selected').data('maxjs');
        let minoutjs = $(this).find('option:selected').data('minoutjs');
        let maxoutjs = $(this).find('option:selected').data('maxoutjs');
        let brands = $(this).find('option:selected').data('brand');
        let types = $(this).find('option:selected').data('type');

        price_field.options.minimumValue(0);
        if (isjawasumatra == 1) {
            item_min_price.text((Number.isInteger(minjs)) ? setRupiah(minjs) : '-');
            item_max_price.text((Number.isInteger(maxjs)) ? setRupiah(maxjs) : '-');
            item_max_price.attr("data-max",maxjs);
            price.prop('disabled', false)
        } else if (isjawasumatra == 0) {
            item_min_price.text((Number.isInteger(minoutjs)) ? setRupiah(minoutjs) : '-');
            item_max_price.text((Number.isInteger(maxoutjs)) ? setRupiah(maxoutjs) : '-');
            item_max_price.attr("data-max",maxoutjs);

            price.prop('disabled', false)
        } else {
            item_min_price.text('-');
            item_max_price.text('-');
            item_max_price.attr("data-max",0);

            price.prop('disabled', true)
        }

        // set pilih merk selection
        $('.select_budget_brand').empty();
        $('.select_budget_brand').prop('disabled',true);
        if(brands != null){
            if(brands.length > 0) {
                brands.forEach((brand) => {
                    $('.select_budget_brand').append('<option value="'+brand.name+'">'+brand.name+'</option>');
                });
            }
            $('.select_budget_brand').append('<option value="-1">Merk Lain</option>');
            $('.select_budget_brand').prop('disabled',false);
        }
        $('.select_budget_brand').trigger('change');

        // set pilih tipe selection
        $('.select_budget_type').empty();
        $('.select_budget_type').prop('disabled',true);
        if(types !=null){
            if(types.length > 0) {
                types.forEach((type) => {
                    $('.select_budget_type').append('<option value="'+type.name+'">'+type.name+'</option>');
                });
            }
            $('.select_budget_type').append('<option value="-1">Tipe Lain</option>');
            $('.select_budget_type').prop('disabled',false);
        }

        // validate kalo namanya apar tunjukin expirednya
        if($(this).find('option:selected').text().trim() == "Apar"){
            $('.budget_expired_field').show();
        }else{
            $('.budget_expired_field').hide();
        }
        $('.budget_expired_date').val("");
        $('.select_budget_type').trigger('change');
    });

    $('.select_budget_brand').on('change',()=>{
        let value_brand = $('.select_budget_brand').val();

        $('.budget_ba_field').hide();
        $('.input_budget_brand_field').hide();
        $('.input_budget_brand').val('');
        if(value_brand == -1){
            $('.input_budget_brand_field').show();
        }
        checkNeedBA();
    });

    $('.select_budget_type').on('change',()=>{
        let value_type = $('.select_budget_type').val();
        $('.budget_ba_field').hide();
        $('.input_budget_type_field').hide();
        $('.input_budget_type').val('');
        if(value_type == -1){
            $('.input_budget_type_field').show();
        }
        checkNeedBA();
    });

    $('.budget_ba_file').on('change', function (event) {
        var reader = new FileReader();
        let value = $(this).val();
        if(validatefilesize(event)){
            reader.onload = function(e) {
                temp_ba_file = e.target.result;
                temp_ba_extension = value.split('.').pop().toLowerCase();
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });

    $('.budget_olditem_file').on('change', function (event) {
        var reader = new FileReader();
        let value = $(this).val();
        if(validatefilesize(event)){
            reader.onload = function(e) {
                temp_olditem_file = e.target.result;
                temp_olditem_extension = value.split('.').pop().toLowerCase();
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });

    $('.nonbudget_olditem_file').on('change', function (event) {
        var reader = new FileReader();
        let value = $(this).val();
        if(validatefilesize(event)){
            reader.onload = function(e) {
                temp_nonbudget_olditem_file = e.target.result;
                temp_nonbudget_olditem_extension = value.split('.').pop().toLowerCase();
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });

    $('.vendor_ba_file').on('change', function (event) {
        var reader = new FileReader();
        let value = $(this).val();
        if(validatefilesize(event)){
            reader.onload = function(e) {
                $('#vendor_ba_preview').prop('href',e.target.result);
                $('#vendor_ba_preview').prop('download','berita_acara_vendor.'+value.split('.').pop().toLowerCase());
                $('#vendor_ba_preview').show();
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
            $('#vendor_ba_preview').prop('href',"");
            $('#vendor_ba_preview').prop('download',"");
            $('#vendor_ba_preview').hide();
        }
    });

    $('#attachment_file_input').change(function(event){
        var reader = new FileReader();
        if(validatefilesize(event)){
            reader.onload = function(e) {
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });

    $(this).on('click','.remove_attachment', function (event) {
        $(this).closest('div').remove();
    });
});
// filesmodal control
$(document).ready(function () {
    $(this).on('click','.filesbutton', function (event) {
        let item_position = $(this).closest('.item_list').index('.item_list');
        let files = $(this).closest('.item_list').data('files');
        if(files){
            files.forEach(file => {
                $('#filesmodal .tablefiles tbody tr').each(function(){
                    if($(this).data('file_completement_id')==file.file_completement_id){
                        $(this).attr('data-id', file.id);
                        $(this).find('.file_check').prop('checked',true);
                        $(this).find('.file_button_upload').prop('disabled',false);
                        $(this).find('.file_url').prop('href',file.file);
                        $(this).find('.file_url').prop('download',file.name);
                        $(this).find('.file_url').text(file.name);
                    }
                });
            });
        }else{
        }
        $('#filesmodal').find('.itempos').val(item_position);
        $('#file_row_filter').val('').trigger('change');
        $('#filesmodal').modal('show');
    });

    $(this).on('change','.file_check', function (event) {
        let tr = $(this).closest('tr');
        tr.attr('data-id',undefined);
        if($(this).prop('checked')){
            tr.find('.file_button_upload').prop('disabled', false);
        }else{
            tr.find('.file_button_upload').prop('disabled', true);
        }
        tr.find('.file_url').prop('href','');
        tr.find('.file_url').prop('download','');
        tr.find('.file_url').text('-');
    });

    $(this).on('click','.file_button_upload', function (event) {
        $(this).closest('tr').find('.inputFile').click();
    });

    $(this).on('change','.inputFile', function(event){
        var reader = new FileReader();
        let value = $(this).val();
        let tr = $(this).closest('tr');
        if(validatefilesize(event)){
            reader.onload = function(e) {
                tr.attr('data-id',undefined);
                tr.find('.file_url').prop('href',e.target.result);
                let name = value.split('\\').pop().toLowerCase();
                tr.find('.file_url').prop('download',name);
                tr.find('.file_url').text(name);
            }
            reader.readAsDataURL(event.target.files[0]);
        }else{
            $(this).val('');
        }
    });

    $(this).on('click','.button_save_files', function(){
        let parent = $(this).closest('.modal');
        let count = 0;
        let data_files = [];
        parent.find('.file_check:checked').each(function(){
            let tr = $(this).closest('tr');
            let file_completement_id = tr.data('file_completement_id');
            let file_name = tr.data('name');
            let id = tr.data('id');
            if(tr.find('.file_url').prop('download')!=""){
                let data;
                data = {
                    id: id,
                    file_completement_id: file_completement_id,
                    file: tr.find('.file_url').prop('href'),
                    name: tr.find('.file_url').prop('download'),
                    filename: file_name,
                };
                data_files.push(data);
                count++;
            }
        });
        // if(count<1){
        //     alert('Pilih kelengkapan berkas minimal 1');
        //     return;
        // }
        let position = parent.find('.itempos').val();
        $('.item_list').eq(position).data('files',data_files);
        let attachment_string ="<table class='other_attachments small table table-sm table-borderless'><tbody>";
        data_files.forEach((data,index)=>{
            attachment_string += "<tr><td>"+data.filename+"</td>"
            attachment_string += "<td><a href='"+data.file+"' download='"+data.name+"'>tampilkan</a></td></tr>";
        });
        attachment_string += "</tbody></table>";
        $('.item_list').eq(position).find('td').eq(6).find('.other_attachments').remove();
        $('.item_list').eq(position).find('td').eq(6).append(attachment_string);
        resetfilesmodal();
        $('#filesmodal').modal('hide');
    });
    $('#filesmodal').on('hide.bs.modal', function (event) {
        resetfilesmodal();
    });
});
function resetfilesmodal(){
    $('#filesmodal').find('.itempos').val('');
    $('#filesmodal').find('tr').attr('data-id',undefined);
    $('#filesmodal').find('.file_check').prop('checked',false);
    $('#filesmodal').find('.file_button_upload').prop('disabled',true);
    $('#filesmodal').find('.inputFile').val('');
    $('#filesmodal').find('.file_url').prop('href','');
    $('#filesmodal').find('.file_url').prop('download','');
    $('#filesmodal').find('.file_url').text('-');
}

function checkNeedBA(){
    let value_brand = $('.select_budget_brand').val();
    let value_type = $('.select_budget_type').val();
    let brands = $('.select_budget_item').find('option:selected').data('brand');
    let types = $('.select_budget_item').find('option:selected').data('type');
    // check apakah butuh BA
    let is_ba_required = false;
    if(brands != null){
        if(brands.length>0 && value_brand == -1){
            is_ba_required = true;
        }
    }
    if(types != null){
        if(types.length>0 && value_type == -1){
            is_ba_required = true;
        }
    }
    if(is_ba_required){
        $('.budget_ba_field').show();
    }else{
        $('.budget_ba_field').hide();
    }
}

function addBudgetItem(el) {
    let select_item = $('.select_budget_item');
    let select_budget_brand = $('.select_budget_brand');
    let select_budget_type = $('.select_budget_type');
    let input_budget_brand = $('.input_budget_brand');
    let input_budget_type = $('.input_budget_type');
    let price_item = AutoNumeric.getAutoNumericElement('.price_budget_item');
    let budget_ba_field = $('.budget_ba_field');
    let budget_olditem_field = $('.budget_olditem_field');
    let budget_expired_date = $('.budget_expired_date');
    let count_item = $('.count_budget_item');
    let table_item = $('.table_item');
    let item_max_price = $('.item_max_price').data("max");

    // console.log(item_max_price.data("max"));

    let id = select_item.find('option:selected').val();
    let name = select_item.find('option:selected').text().trim();
    let price = price_item.get();
    let price_text = price_item.domElement.value;
    let count = count_item.val();
    let is_it = select_item.find('option:selected').data('is_it');
    let it_alias = select_item.find('option:selected').data('it_alias');

    let brand = select_budget_brand.val();
    let type = select_budget_type.val();
    if(brand == -1){
        brand = input_budget_brand.val().trim();
    }else{
        brand = brand;
    }
    if(type == -1){
        type = input_budget_type.val().trim();
    }else{
        type = type;
    }

    if (id == "") {
        alert("Item harus dipilih");
        return;
    }
    if(budget_olditem_field.is(':visible') && temp_olditem_file == null){
        alert("File Foto Item Lama harus diupload");
        return;
    }
    if (brand == "") {
        alert("Pilihan Merk harus dipilih / diisi");
        return;
    }
    if (type == "") {
        alert("Pilihan Tipe harus dipilih / diisi");
        return;
    }
    if(budget_ba_field.is(':visible') && temp_ba_file == null){
        alert("File Berita Acara Harus diupload");
        return;
    }
    if (price < 1000) {
        alert("Harga harus lebih besar dari Rp 1.000");
        return;
    }
    if (count < 1) {
        alert("Jumlah Item minimal 1");
        return;
    }
    let attachments_link = "";
    if(budget_ba_field.is(':visible')){
        attachments_link  += '<a class="attachment" href="'+temp_ba_file+'" download="ba_file.'+temp_ba_extension+'">ba_file.'+temp_ba_extension+'</a><br>';
    }
    if(budget_olditem_field.is(':visible')){
        attachments_link  += '<a class="attachment" href="'+temp_olditem_file+'" download="old_item.'+temp_olditem_extension+'">old_item.'+temp_olditem_extension+'</a><br>';
    }
    if(!budget_ba_field.is(':visible') && !budget_olditem_field.is(':visible')){
        attachments_link = '-';
    }
    let naming = name;
    if(budget_expired_date.val()!=""){
        naming = name+'<br>(expired : '+budget_expired_date.val()+')';
    }
    // tbody eq(0) supaya ga nyasar ke table other attachment
    table_item.find('tbody:eq(0)').append('<tr class="item_list" data-budget_pricing_id="' + id + '" data-name="' + name + '" data-price="' + price + '" data-count="' + count + '" data-brand="' + brand + '" data-type="' + type + '" data-max="' + item_max_price + '" data-expired="'+budget_expired_date.val()+'" data-is_it="' + is_it + '" data-it_alias="' + it_alias + '"><td>'+naming+'</td><td>' + brand + '</td><td>' + type + '</td><td>' + price_text + '</td><td>' + count + '</td><td>' + setRupiah(count * price) + '</td><td>' + attachments_link + '</td><td><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i><button type="button" class="btn btn-primary btn-sm filesbutton">kelengkapan berkas</button></td></tr>');

    select_item.val("");
    select_item.trigger('change');
    price_item.set(0);
    count_item.val("");
    input_budget_brand.val("");
    input_budget_type.val("");
    $('.budget_ba_file').val('');
    $('.budget_olditem_file').val('');
    budget_expired_date.val('');
    temp_ba_file = null;
    temp_ba_extension = null;
    temp_olditem_file = null;
    temp_olditem_extension = null;
    tableRefreshed(el);
}

function addAttachment() {
    if($('#attachment_file_input').val() == null || $('#attachment_file_input').val() == '') {
        alert('File attachment belum dipilih');
        return;
    }
    let filename = $('#attachment_file_input')[0].files[0].name;
    let file = null;
    var reader = new FileReader();
    reader.onload = function(e) {
        file = e.target.result;
        $('#attachment_list').append('<div><a class="opt_attachment" href="'+file+'" download="'+filename+'">'+filename+'</a><span class="remove_attachment">X</span></div>');
        $('#attachment_file_input').val('');
    }
    reader.readAsDataURL($('#attachment_file_input')[0].files[0]);
}

// add non budget
function addNonBudgetItem(){
    const it_default_items = ['PC Desktop', 'Server', 'Notebook', 'Monitor', 'UPS', 'Printer INK Copy Scan',
        'Printer Dot Matriks', 'HandHelt / Handset', 'Finger Scan', 'Dvd Eksternal', 'Hdd Eksternal',
        'Memory', 'Etc'
    ];
    let is_it = $('.is_it').val();
    let name = $('.input_nonbudget_name').val().trim();
    let brand = $('.input_nonbudget_brand').val().trim();
    let type = $('.input_nonbudget_type').val().trim();
    let price = AutoNumeric.getAutoNumericElement('.price_nonbudget_item');
    let price_text = price.domElement.value;
    let count = $('.count_nonbudget_item').val();

    if (name == "") {
        alert("Nama item harus diisi");
        return;
    } else if($('.nonbudget_olditem_field').is(':visible') && temp_nonbudget_olditem_file == null){
        alert("File Foto Item Lama harus diupload");
        return;
    } else if (brand == "") {
        alert("Merk harus diisi");
        return;
    } else if (type == "") {
        alert("Tipe harus diisi");
        return;
    } else if (price < 1000) {
        alert("Harga harus lebih besar dari Rp 1.000");
        return;
    } else if (count < 1) {
        alert("Jumlah Item minimal 1");
        return;
    } else if (is_it == true && it_default_items.includes(name)) {
        alert("Nama Item IT non budget harus berbeda dengan nama item default (FORM FRI");
        return;
    } else {

    let attachments_link = "";
    if($('.nonbudget_olditem_field').is(':visible')){
        attachments_link  += '<a class="attachment" href="'+temp_nonbudget_olditem_file+'" download="old_item.'+temp_nonbudget_olditem_extension+'">old_item.'+temp_nonbudget_olditem_extension+'</a><br>';
    }
    if(!$('.nonbudget_olditem_field').is(':visible')){
        attachments_link = '-';
    }
    $('.table_item tbody:eq(0)').append('<tr class="item_list" data-name="' + name + '" data-brand="' + brand + '" data-type="' + type + '" data-price="' + price.get() + '" data-count="' + count + '"><td>' + name + '</td><td>' + brand + '</td><td>' + type + '</td><td>' + price_text + '</td><td>' + count + '</td><td>' + setRupiah(count * price.get()) + '</td><td>' + attachments_link + '</td><td><i class="fa fa-trash text-danger remove_list" onclick="removeList(this)" aria-hidden="true"></i><button type="button" class="btn btn-primary btn-sm filesbutton">kelengkapan berkas</button></td></tr>');

    $('.input_nonbudget_name').val('');
    $('.input_nonbudget_brand').val('');
    $('.input_nonbudget_type').val('');
    $('.count_nonbudget_item').val('');
    $('.nonbudget_olditem_file').val('');
    price.set(0);
    temp_nonbudget_olditem_file = null;
    temp_nonbudget_olditem_extension = null;

    tableRefreshed();
    }
}

// add maintenance item
function addMaintenanceItem(el) {
    if ($('#maintenance_item_select').val() == "") {
        alert("Item harus dipilih");
        return;
    }
    let price = AutoNumeric.getAutoNumericElement('#maintenance_price_input');
    if (price.get() < 1000) {
        alert("Harga harus lebih besar dari Rp 1.000");
        return;
    }
    if ($('#maintenance_count_input').val() < 1) {
        alert("Jumlah Item minimal 1");
        return;
    }
    // tbody eq(0) supaya ga nyasar ke table other attachment
    let name = $('#maintenance_item_select option:selected').text().trim();
    let count = $('#maintenance_count_input').val();
    let brand = $('#maintenance_brand_input').val();
    let type = $('#maintenance_type_input').val();
    let is_it = $('#maintenance_item_select option:selected').data('is_it');
    let it_alias = $('#maintenance_item_select option:selected').data('it_alias');

    $('.table_item tbody:eq(0)').append('<tr class="item_list" data-maintenance_budget_id="'+$('#maintenance_item_select').val()+'" data-name="' + name + '" data-price="' + price.get() + '" data-count="' + count + '" data-brand="' + brand + '" data-type="' + type + '" data-is_it="' + is_it + '" data-it_alias="' + it_alias + '"><td>'+name+'</td><td>' + brand + '</td><td>' + type + '</td><td>' + setRupiah(price.get()) + '</td><td>' + count + '</td><td>' + setRupiah(count * price.get()) + '</td><td></td><td><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i><button type="button" class="btn btn-primary btn-sm filesbutton">kelengkapan berkas</button></td></tr>');

    $('#maintenance_item_select').val('');
    $('#maintenance_item_select').trigger('change');
    $('#maintenance_brand_input').val('');
    $('#maintenance_type_input').val('');
    $('#maintenance_price_input').val('');
    $('#maintenance_count_input').val('');
    tableRefreshed(el);
}

// add ho item
function addHOItem(el) {

    if ($('#ho_item_select').val() == "") {
        alert("Item harus dipilih");
        return;
    }
    let price = AutoNumeric.getAutoNumericElement('#ho_price_input');
    if (price.get() < 1000) {
        alert("Harga harus lebih besar dari Rp 1.000");
        return;
    }
    if ($('#ho_count_input').val() < 1) {
        alert("Jumlah Item minimal 1");
        return;
    }
    // tbody eq(0) supaya ga nyasar ke table other attachment
    let name = $('#ho_item_select option:selected').text().trim();
    let count = $('#ho_count_input').val();
    let brand = $('#ho_brand_input').val();
    let type = $('#ho_type_input').val();
    let is_it = $('#ho_item_select option:selected').data('is_it');
    let it_alias = $('#ho_item_select option:selected').data('it_alias');

    $('.table_item tbody:eq(0)').append('<tr class="item_list" data-ho_budget_id="'+$('#ho_item_select').val()+'" data-name="' + name + '" data-price="' + price.get() + '" data-count="' + count + '" data-brand="' + brand + '" data-type="' + type + '" data-is_it="' + is_it + '" data-it_alias="' + it_alias + '"><td>'+name+'</td><td>' + brand + '</td><td>' + type + '</td><td>' + setRupiah(price.get()) + '</td><td>' + count + '</td><td>' + setRupiah(count * price.get()) + '</td><td></td><td><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i><button type="button" class="btn btn-primary btn-sm filesbutton">kelengkapan berkas</button></td></tr>');

    $('#ho_item_select').val('');
    $('#ho_item_select').trigger('change');
    $('#ho_brand_input').val('');
    $('#ho_type_input').val('');
    $('#ho_price_input').val('');
    $('#ho_count_input').val('');
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
    let authorization_select = $('.authorization_select2');
    let item_select = $('.item_type');
    let request_select = $('.request_type');
    let budget_select = $('.budget_type');
    // check table level if table has data / tr or not
    let row_count = 0;
    table_item.find('tbody:eq(0) tr').not('.empty_row').each(function () {
        row_count++;
    });
    if (row_count > 0) {
        salespoint_select.prop('disabled',true);
        // authorization_select.prop('disabled',true);
        item_select.prop('disabled',true);
        request_select.prop('disabled',true);
        budget_select.prop('disabled',true);
        $('.is_it').prop('disabled',true);
        table_item.find('.empty_row').remove();
    } else {
        salespoint_select.prop('disabled',false);
        // authorization_select.prop('disabled',false);
        item_select.prop('disabled',false);
        request_select.prop('disabled',false);
        budget_select.prop('disabled',false);
        $('.is_it').prop('disabled',false);
        table_item.find('tbody:eq(0)').append('<tr class="empty_row text-center"><td colspan="8">Item belum dipilih</td></tr>');
        if(item_select.val() == 2){
            // maintenance
            request_select.prop('disabled',true);
            budget_select.prop('disabled',true);
        }
    }
}

// add vendor
function addVendor(el){
    let select_vendor = $('.select_vendor');
    let table_vendor = $('.table_vendor');
    let id = select_vendor.find('option:selected').data('id');
    let name = select_vendor.find('option:selected').data('name');
    let code = select_vendor.find('option:selected').data('code');
    let salesperson = select_vendor.find('option:selected').data('salesperson');
    if(select_vendor.val()==""){
        alert('Harap pilih vendor terlebih dulu');
        return;
    }
    if($('.vendor_item_list').length>2){
        alert('Maksimal 3 vendor');
        return;
    }

    $flag_exists = false;
    $('.vendor_item_list').each(function(){
        let existing_name = $(this).find('td:eq(1)').text().trim();
        if(name.toLowerCase()==existing_name.toLowerCase()){
            alert('Vendor sudah dipilih sebelumnya');
            $flag_exists = true;
        }
    });
    if($flag_exists){
        return;
    }
    table_vendor.find('tbody').append('<tr class="vendor_item_list" data-vendor_id="'+id+'"><td>'+code+'</td><td>'+name+'</td><td>'+salesperson+'</td><td>-</td><td>Terdaftar</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>');
    select_vendor.val('');
    select_vendor.trigger('change');
    tableVendorRefreshed(select_vendor);
}

function addOTVendor(el){
    let vendor_name = $('.ot_vendor_name');
    let vendor_sales = $('.ot_vendor_sales');
    let vendor_phone = $('.ot_vendor_phone');
    let table_vendor = $('.table_vendor');
    if(vendor_name.val()==""){
        alert('Nama Vendor tidak boleh kosong');
        return;
    }
    if(vendor_sales.val()==""){
        alert('Sales Vendor tidak boleh kosong');
        return;
    }
    if(vendor_phone.val()==""){
        alert('Telfon Vendor tidak boleh kosong');
        return;
    }
    if($('.vendor_item_list').length>1){
        alert('Maksimal 2 vendor');
        return;
    }
    table_vendor.find('tbody').append('<tr class="vendor_item_list" data-name="'+vendor_name.val()+'" data-sales="'+vendor_sales.val()+'" data-phone="'+vendor_phone.val()+'"><td>-</td><td>'+vendor_name.val()+'</td><td>'+vendor_sales.val()+'</td><td>'+vendor_phone.val()+'</td><td>One Time</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
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
        table_vendor.find('tbody').append('<tr class="empty_row text-center"><td colspan="6">Vendor belum dipilih</td></tr>');
    }
    if($('.vendor_item_list').length<2){
        $('.vendor_ba_field').show();
    }else{
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

    $('.item_list').each(function() {
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

    if(salespoint_over_budget != 251 || salespoint_over_budget != 252){
        $('#approval_over_budget_area').show();
    } else {
        $('#approval_over_budget_area').hide();
    }
}

function addRequest(type){
    let hasil = overBudgetPopUp();

    if(type == 3){
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
    input_field.append('<input type="hidden" name="type" value="'+type+'">')
    input_field.append('<input type="hidden" name="requirement_date" value="'+$('.requirement_date').val()+'">');
    input_field.append('<input type="hidden" name="salespoint" value="'+$('.salespoint_select2').val()+'">');
    input_field.append('<input type="hidden" name="authorization" value="'+$('.authorization_select2').val()+'">');
    input_field.append('<input type="hidden" name="item_type" value="'+$('.item_type').val()+'">');
    input_field.append('<input type="hidden" name="request_type" value="'+$('.request_type').val()+'">');
    input_field.append('<input type="hidden" name="is_it" value="'+$('.is_it').val()+'">');
    input_field.append('<input type="hidden" name="budget_type" value="'+$('.budget_type').val()+'">');
    input_field.append('<input type="hidden" name="division" value="'+$('.division_select').val()+'">');
    input_field.append('<input type="hidden" name="indirect_salespoint_id" value="'+$('.indirect_salespoint').val()+'">');
    input_field.append('<input type="hidden" name="reason" value="'+$('.reason').val()+'">');
    input_field.append('<input type="hidden" name="is_over_budget" value="'+hasil+'">');
    input_field.append('<input type="hidden" name="is_over_budget_hidden" value="'+$('.is_over_budget_hidden').val()+'">');

    let reason_over_budget = $('.reason_over_budget').val();
    input_field.append('<input type="hidden" name="reason_over_budget" value="'+reason_over_budget+'">');

    item_list.each(function(index,el){
        input_field.append('<input type="hidden" name="item['+index+'][id]" value="'+$(el).data('id')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][budget_pricing_id]" value="'+$(el).data('budget_pricing_id')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][maintenance_budget_id]" value="'+$(el).data('maintenance_budget_id')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][ho_budget_id]" value="'+$(el).data('ho_budget_id')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][name]" value="'+$(el).data('name')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][price]" value="'+$(el).data('price')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][count]" value="'+$(el).data('count')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][brand]" value="'+$(el).data('brand')+'">');
        input_field.append('<input type="hidden" name="item['+index+'][type]" value="'+$(el).data('type')+'">');

        input_field.append('<input type="hidden" name="item['+index+'][item_max_price]" value="'+$(el).data('max')+'">');

        $(el).find('.attachment').each(function(att_index,att_el){
            let filename = $(att_el).prop('download');
            let file = $(att_el).prop('href');
            input_field.append('<input type="hidden" name="item['+index+'][attachments]['+att_index+'][filename]" value="'+filename+'">');
            // base 64 data
            input_field.append('<input type="hidden" name="item['+index+'][attachments]['+att_index+'][file]" value="'+file+'">');
        });
        if($(el).data('files')){
            $(el).data('files').forEach((file, index_file) =>{
                input_field.append('<input type="hidden" name="item['+index+'][files]['+index_file+'][id]" value="'+file.id+'">');
                input_field.append('<input type="hidden" name="item['+index+'][files]['+index_file+'][file_completement_id]" value="'+file.file_completement_id+'">');
                input_field.append('<input type="hidden" name="item['+index+'][files]['+index_file+'][name]" value="'+file.name+'">');
                input_field.append('<input type="hidden" name="item['+index+'][files]['+index_file+'][file]" value="'+file.file+'">');
            });
        }
    });

    vendor_item_list.each(function(index, el) {
        input_field.append('<input type="hidden" name="vendor['+index+'][id]" value="'+$(el).data('id')+'">');
        input_field.append('<input type="hidden" name="vendor['+index+'][vendor_id]" value="'+$(el).data('vendor_id')+'">');
        input_field.append('<input type="hidden" name="vendor['+index+'][name]" value="'+$(el).data('name')+'">');
        input_field.append('<input type="hidden" name="vendor['+index+'][sales]" value="'+$(el).data('sales')+'">');
        input_field.append('<input type="hidden" name="vendor['+index+'][phone]" value="'+$(el).data('phone')+'">');
    });

    if(vendor_item_list.length<2){
        let filename = $('#vendor_ba_preview').prop('download');
        let file = $('#vendor_ba_preview').prop('href');
        if(filename != "null"){
            input_field.append('<input type="hidden" name="ba_vendor_name" value="'+filename+'">');
            input_field.append('<input type="hidden" name="ba_vendor_file" value="'+file+'">');
        }
    }

    $('.opt_attachment').each(function(index,el){
        let file = $(el).prop('href');
        let filename = $(el).prop('download');
        input_field.append('<input type="hidden" name="opt_attach['+index+'][file]" value="'+file+'">');
        input_field.append('<input type="hidden" name="opt_attach['+index+'][name]" value="'+filename+'">');
    });

    // FRI FORM
    if($('.is_it').val() == true){
        input_field.append($('#fri_form_create input').clone());
    }
    $('#addform').submit();
}

function approve(){
    $('#approveform').submit();
}

function reject(){
    var reason = prompt("Harap memasukan alasan penolakan");
    if (reason != null) {
        if(reason.trim() == ''){
            alert("Alasan Harus diisi");
            return;
        }
        $('#rejectform .input_field').append('<input type="hidden" name="reason" value="'+reason+'">');
        $('#rejectform').submit();
    }
}

