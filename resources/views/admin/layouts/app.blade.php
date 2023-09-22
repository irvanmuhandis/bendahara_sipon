<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keuangan PP KGS</title>
    <link rel="icon" type="image/x-icon" href="{{ url('/images/cuak.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper" id="app">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ url('/images/icon_web.png') }}" height="100" width="100"
                id="preload">
        </div>
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
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


        <aside class="main-sidebar sidebar-light-primary elevation-2">

            <a href="#" class="brand-link">
                <img src="{{ asset('images/icon_web.png') }}" alt="Keuangan logo" class="brand-image "
                    style="opacity: .8">
                <span class="brand-text font-weight-light">KEUANGAN</span>
            </a>

            <div class="sidebar">

                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://ui-avatars.com/api/?name={{$operator['nickname']}}" class="img-circle"
                            alt="User Image">

                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{$operator['nickname']}}</a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <router-link to="/"  :class="{ 'active': $route.path === '/' }" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/admin/income"
                                :class="$route.path.startsWith('/admin/income') ? 'active' : ''" active-class="active"
                                class="nav-link">
                                <i class="nav-icon fas fa-arrow-down"></i>
                                <p>
                                    Pemasukan
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/admin/expense" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-arrow-up"></i>
                                <p>
                                    Pengeluaran
                                </p>
                            </router-link>
                        </li>

                        <li class="nav-item"><a href="#"
                                :class="$route.path.startsWith('/admin/report') ? 'active' : ''" to="/admin/report"
                                class="nav-link"><i class="nav-icon fas  fa-book-open"></i>
                                <p> Laporan <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview"
                                style="background: rgb(255, 255, 255);border:grey solid 2px;border-radius:8px">
                                <li class="nav-item">
                                    <router-link to="/admin/report/arrear" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-book"></i>
                                        <p>
                                            Tunggakan
                                        </p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/report/money"
                                        :class="$route.path.startsWith('/admin/money') ? 'active' : ''"
                                        active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-chart-line"></i>
                                        <p>
                                            Keuangan
                                        </p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item"><a href="#"
                                :class="$route.path.startsWith('/admin/billing') ? 'active' : ''" to="/admin/billings"
                                class="nav-link"><i class="nav-icon fas fa-money-bill"></i>
                                <p> Tagihan <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview"
                                style="background: rgb(255, 255, 255);border:grey solid 2px;border-radius:8px">
                                <li class="nav-item">
                                    <router-link to="/admin/billing/debt" active-class="active"
                                        :class="$route.path.startsWith('/admin/billing/debt') ? 'active' : ''"
                                        class="nav-link">
                                        <i class="nav-icon fas fa-piggy-bank"></i>
                                        <p>
                                            Piutang
                                        </p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/billing/bill"
                                        :class="$route.path.startsWith('/admin/billing/bill') ? 'active' : ''"
                                        active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-money-check-alt"></i>
                                        <p>
                                            Tagihan
                                        </p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item"><a href="#"
                                :class="$route.path.startsWith('/admin/master') ? 'active' : ''" to="/admin/master"
                                class="nav-link"><i class="nav-icon fas fa-tools"></i>
                                <p> Master <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview"
                                style="background: rgb(255, 255, 255);border:grey solid 2px;border-radius:8px">
                                {{-- <li class="nav-item">
                                    <router-link :class="$route.path.startsWith('/admin/master/group') ? 'active' : ''"
                                        to="/admin/master/group" active-class="active" class="nav-link">

                                        <i class="nav-icon fas fa-sitemap"></i>
                                        <p>Grup</p>
                                    </router-link>
                                </li> --}}
                                <li class="nav-item">
                                    <router-link
                                        :class="$route.path.startsWith('/admin/master/account') ? 'active' : ''"
                                        to="/admin/master/account" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>
                                            Akun
                                        </p>
                                    </router-link>
                                </li>


                                <li class="nav-item">
                                    <router-link :class="$route.path.startsWith('/admin/master/wallet') ? 'active' : ''"
                                        to="/admin/master/wallet" active-class="active" class="nav-link">
                                        <i class="nav-icon fas fa-wallet"></i>
                                        <p>
                                            Dompet
                                        </p>
                                    </router-link>
                                </li>

                            </ul>
                        </li>
                        <li class="nav-item">
                            <router-link :class="$route.path.startsWith('/admin/dispens') ? 'active' : ''"
                                to="/admin/dispens" active-class="active" class="nav-link">
                                <i class="nav-icon fas fa-scroll"></i>
                                <p>
                                    Surat Dispensasi
                                </p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <form action="/logout" method="get">
                                <button active-class="active" type="submit" class="nav-link">
                                    <i class="nav-icon fas fa-sign-out-alt"></i>
                                    <p>
                                        Logout
                                    </p>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

            </div>

        </aside>

        <div class="content-wrapper bg-white">
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

            {{-- <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>

            <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights
            reserved. --}}
        </footer>
    </div>


</body>

</html>
