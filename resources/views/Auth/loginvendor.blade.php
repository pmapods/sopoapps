<html><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pinus Merah Abadi</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/logo.png" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../../../vendor/almasaeed2010/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <style>
        .login-page{
            background: url('/assets/login-vendor.jpg');
            background-size: cover;
            background-position: center;
            /* background-color: aqua */
        }
        .login-box, .register-box {
            width: 500px !important;
            border-radius: 5em;
        }
    </style>
  </head>
  <body class="login-page">
  <div class="login-box p-5" style="background-color: #2b090a6b">
    
    @if(Session::has('success'))
        <div class="m-1 alert alert-success alert-dismissible fade show" role="alert">
            {{Session::get('success')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(Session::has('error'))
        <div class="m-1 alert alert-danger alert-dismissible fade show" role="alert">
            {{Session::get('error')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="login-logout">
        <div class="d-flex flex-row">
            <img src="/assets/logo.png" width="120" alt="">
            <div class="d-flex flex-column justify-content-center text-light">
                <span class="display-4">PMA-PODS Auction</span>
            </div>
        </div>
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <form action="/auction/doLoginVendor" method="post">
          {{ csrf_field() }}
          <div class="input-group mb-3">
            <input type="text" class="form-control" name="username" placeholder="Username / Email" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fad fa-user"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col">
              <button type="submit" class="btn btn-danger btn-block">Masuk</button>
              <small class="d-block text-center mt-3" style="color: #000000">Belum punya akun? <a href="/auction/register" style="color: #9A0707">Register</a></small>
              <small class="d-block text-center mt-3"> <a href="/auction/auctionTicket" style="color: #9A0707"> <i class="fa-solid fa-house"></i> Home</a> </small>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<!-- Bootstrap 4 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.0.5/js/adminlte.min.js"></script>
  </body></html>
