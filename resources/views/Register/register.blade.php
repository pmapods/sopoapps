@extends('Layout.register')
@section('container')
<div class="container register">
    <!-- diplay success and error messages -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-3 register-left">
            <img src="{{ asset('assets/logo.png') }}" alt=""/>
            <h3 style="color: #000000">PMA - PODS</h3>
            <small class="d-block text-center mt-3" style="color: #000000">Sudah Registrasi? <a href="/auction/login" style="color: #FFFFFF">Login</a></small>
        </div>
        <div class="col-md-9 register-right">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <form action="/auction/addVendorCompany" method="post">
                        @csrf
                        <h3 class="register-heading">Informasi Perusahaan</h3>
                        <div class="row register-form">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select class="form-control select2" name="vendor_ref">
                                        <option value="">-- Pilih Jika sudah pernah menjadi vendor PMA --</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->code }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Nama Perusahaan *" value="" required autofocus/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="company_leader" name="company_leader" placeholder="Nama Pimpinan Perusahaan *" value="" required/>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" id="company_address" name="company_address" placeholder="Alamat Perusahaan *" value="" required></textarea>
                                </div>
                                <div class="form-group">
                                    <select class="form-control select2" name="city_id">
                                        <option value="">-- Pilih Kota --</option>
                                        @foreach ($provinces as $province)
                                            <optgroup label="{{ $province->name }}">
                                                @foreach ($province->regencies as $regency)
                                                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="tel" class="form-control" minlength="10" maxlength="14" id="contact_number" name="contact_number" pattern="[0-9]*" placeholder="No. Telp *" value="" required/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="website" name="website"  placeholder="Website" value=""/>
                                </div>
                                <div class="form-group">
                                    <select class="form-select" id="company_status" name="company_status" required>
                                        <option value="" selected disabled >Status Perusahaan *</option>
                                        <option value="Pusat">Pusat</option>
                                        <option value="Cabang">Cabang</option>
                                        <option value="Anak Perusahaan">Anak Perusahaan</option>
                                        <option value="Cabang Anak Perusahaan">Cabang Anak Perusahaan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select class="form-select" id="legal_form" name="legal_form" required>
                                        <option value="" selected disabled >Bentuk Badan Hukum *</option>
                                        <option value="PT">PT</option>
                                        <option value="CV">CV</option>
                                        <option value="Perorangan">Perorangan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select class="form-select" id="ownership" name="ownership" required>
                                        <option value="" selected disabled>Status Kepemilikan *</option>
                                        <option value="Swasta Nasional">Swasta Nasional</option>
                                        <option value="Swasta Asing">Swasta Asing</option>
                                        <option value="BUMN">BUMN</option>
                                        <option value="BUMD">BUMD</option>
                                        <option value="Koperasi">Koperasi</option>
                                        <option value="Yayasan">Yayasan</option>
                                        <option value="Franchise">Franchise</option>
                                        <option value="Asosiasi">Asosiasi</option>
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <small class="form-text text-muted">Jenis Usaha *</small>
                                    <div class="col-md-5">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="business_type" id="barangjasa" value="barangjasa" checked>
                                            <label class="form-check-label" for="barangjasa">
                                                Barang Jasa
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="business_type" id="armada" value="armada">
                                            <label class="form-check-label" for="armada">
                                                Armada
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="business_type" id="security" value="security">
                                            <label class="form-check-label" for="security">
                                                Security
                                            </label>
                                        </div>
                                        <!-- <div class="form-check">
                                            <input class="form-check-input" type="radio" name="business_type" id="jasa_lainnya" value="" >
                                            <label class="form-check-label" for="jasa_lainnya">
                                                <input type="text" class="form-control mt-1" name="jasa_lainnya_text" id="jasa_lainnya_text" placeholder="Lainnya" >
                                            </label>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- <div class="form-group"> -->
                                    <small class="form-text text-muted">Upload profil perusahaan *</small>
                                    <input type="file" class="form-control" id="company_profile" name="company_profile" value="" required/>
                                <!-- </div> -->
                                <div class="form-group">
                                    <small class="form-text text-muted">Upload legalitas : Akta Pendirian *</small>
                                    <input type="file" class="form-control" id="legal_docs" name="legal_docs" value="" required/>
                                </div>
                                <div class="form-group">
                                    <small class="form-text text-muted">Upload legalitas : Domisili / Izin Lokasi *</small>
                                    <input type="file" class="form-control" id="location_permission" name="location_permission" value="" required/>
                                </div>
                                <div class="form-group">
                                    <small class="form-text text-muted">Upload legalitas : SIUP / Izin Usaha *</small>
                                    <input type="file" class="form-control" id="siup" name="siup" value="" required/>
                                </div>
                                <div class="form-group">
                                    <small class="form-text text-muted">Upload legalitas : TDP (Tanda Daftar Perusahaan) / NIB*</small>
                                    <input type="file" class="form-control" id="tdp_nib" name="tdp_nib" value="" required/>
                                </div>
                                <div class="form-group">
                                    <small class="form-text text-muted">Upload legalitas : NPWP *</small>
                                    <input type="file" class="form-control" id="company_npwp" name="company_npwp" value="" required/>
                                </div>
                            </div>
                        </div>
                        <h3 class="register-heading-2">PIC Perusahaan</h3>
                        <div class="row register-form">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="pic_name" name="pic_name" placeholder="Nama Lengkap *" value="" required autofocus/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="pic_position" name="pic_position" placeholder="Jabatan *" value="" required autofocus/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Buat username untuk login *" value="" required autofocus/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="tel" class="form-control" minlength="10" maxlength="13" id="pic_phone" name="pic_phone"  placeholder="No. Hp *" value="" required/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="pic_email" name="pic_email" placeholder="Email *" value="" required autofocus/>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" minlength="6" id="password" name="password" placeholder="Password *" value="" required autofocus/>
                                </div>

                                <input type="submit" class="btnRegister btn-danger"  value="Register"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
