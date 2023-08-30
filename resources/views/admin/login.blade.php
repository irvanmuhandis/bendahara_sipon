<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
</head>

<body>
    <div>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <form method="post" action="{{url('login')}}">
            <div class="form-group" method="post" action="/login">
                @csrf
                <label for="exampleInputEmail1">NIS</label>
                <input type="text" name="nis_santri" class="form-control" id="exampleInputEmail1"
                    aria-describedby="emailHelp" placeholder="Enter email">
                <small id="emailHelp" class="form-text text-muted">nis santri</small>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input name="password" type="password" class="form-control" id="exampleInputPassword1"
                    placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
 
</body>
<script>
    // import {
    //     reactive,
    //     ref,
    //     onMounted
    // } from 'vue';
    // import axios from 'axios';


    // const form = ref({
    //     'nis': '0',
    //     'pass': 'sad'
    // })
    // const token = ref();
    // const userId = ref();

    // function login() {
    //     axios.get('/sanctum/csrf-cookie').then(response => {
    //         axios.post('/login', {
    //             nis_santri: form.value.nis,
    //             password: form.value.pass
    //         }).then(response => {
    //             console.log(response.data);
    //             let data = response.data;
    //             localStorage.setItem('token', data.token);
    //             localStorage.setItem('userId', data.userId);
    //             token.value = localStorage.getItem("token") != null ? localStorage.getItem('token') :
    //                 'waw'
    //             userId.value = localStorage.getItem("userId") != null ? localStorage.getItem('userId') :
    //                 'waw'
    //         });
    //     });

    // }

    // function logout() {
    //     axios.get('/logout').then(data => {
    //         console.log(response.data);
    //     })
    // }
</script>

</html>
