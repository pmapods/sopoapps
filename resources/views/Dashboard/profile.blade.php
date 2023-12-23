@extends('Layout.app')
@section('local-css')

@endsection

@section('content')
 <h1>Profile</h1>
 
 <div class="content-body">
     <div class="row">
         <div class="col-md-6 col-12 card p-3">
            <form action="/changepassword" method="post" enctype="multipart/form">
                @csrf
                <h5>Change Password</h5>
               <div class="form-group">
                 <label for="old_password">Password Lama</label>
                 <input type="text" name="old_password" id="old_password" class="form-control">
                 <small class="text-muted">Masukan password lama</small>
               </div>
               <div class="form-group">
                 <label for="new_password">Password Baru</label>
                 <input type="text" name="new_password" id="new_password" class="form-control">
                 <small class="text-muted">Masukan password baru</small>
               </div>
               <div class="form-group">
                 <label for="confirm_new_password">Konfirmasil Password Baru</label>
                 <input type="text" name="confirm_new_password" id="confirm_new_password" class="form-control">
                 <small class="text-muted">Masukan password baru sekali lagi</small>
               </div>
               <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
         </div>
     </div>
 </div>
@endsection
@section('local-js')
<script>
</script>
@endsection
