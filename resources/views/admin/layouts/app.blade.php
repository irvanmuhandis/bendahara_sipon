<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CUAN | Bendahara</title>
    <link rel="icon" type="image/x-icon" href="{{url('/images/cuak.ico')}}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper" id="app">
        <div class="preloader flex-column justify-content-center align-items-center" style="height: 0px;">
            <img class="animation__wobble" src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png"
                alt="AdminLTELogo" height="60" width="60" style="display: none;">
        </div>
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index3.html" class="nav-link">Home</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">Contact</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">


                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>
            </ul>
        </nav>


        <aside class="main-sidebar sidebar-light-primary ">

            <a href="#" class="brand-link">
                <img src="{{ asset('images/icon_web.png') }}" alt="Keuangan logo" class="brand-image "
                    style="opacity: .8">
                <span class="brand-text font-weight-light">KEUANGAN</span>
            </a>

            <div class="sidebar">

                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle"
                            alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">Alexander Pierce</a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <router-link to="/admin/dashboard" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/admin/bigBook" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-book-open"></i>
                                <p>
                                    Big Book
                                </p>
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/admin/trans" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-receipt"></i>
                                <p>
                                    Transaction
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/admin/pay" :class="$route.path.startsWith('/admin/pay') ? 'active' : ''"
                                active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>
                                    Pay
                                </p>
                            </router-link>

                        </li>
                        <li class="nav-item"><a href="#" class="nav-link"><i
                                    class="nav-icon fas fa-money-bill"></i>
                                <p> Tagihan <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview"
                                style="background: rgb(255, 255, 255);border:grey solid 2px;border-radius:8px">
                                <li class="nav-item">
                                    <router-link to="/admin/debt" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-piggy-bank"></i>
                                        <p>
                                            Debt
                                        </p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/bill"
                                        :class="$route.path.startsWith('/admin/bill') ? 'active' : ''"
                                        active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-money-check-alt"></i>
                                        <p>
                                            Bill
                                        </p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item"><a href="#" class="nav-link"><i
                                    class="nav-icon fas fa-tools"></i>
                                <p> Setting <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview"
                                style="background: rgb(255, 255, 255);border:grey solid 2px;border-radius:8px">
                                <li class="nav-item">
                                    <router-link to="/admin/group" active-class="active" class="nav-link">

                                        <i class="nav-icon fas fa-sitemap"></i>
                                        <p>Group</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/users" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>
                                            Users
                                        </p>
                                    </router-link>
                                </li>


                                <li class="nav-item">
                                    <router-link to="/admin/wallet" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-wallet"></i>
                                        <p>
                                            Wallet
                                        </p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/dispens" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-scroll"></i>
                                        <p>
                                            Dispensations
                                        </p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>
                    </ul>
                </nav>

            </div>

        </aside>

        <div class="content-wrapper">
            <router-view></router-view>


        </div>


        <aside class="control-sidebar control-sidebar-dark">

            <div class="p-3">
                <h4>Keuangan v1.0</h4>
                <h5> Copyright &copy; 2023 <a href="https://kyaigalangsewu.net">Kyai Galang Sewu</a></strong> All
                    rights
                    reserved. </h5>
                <p>Coming soon</p>

            </div>
        </aside>


        <footer class="main-footer">

            <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>

            <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights
            reserved.
        </footer>
    </div>


</body>

</html>
