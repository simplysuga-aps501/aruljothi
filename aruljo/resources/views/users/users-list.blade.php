@extends('adminlte::page')

@section('title', 'All Users')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Users</h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">

                <div class="card-body">

                    <div class="table-responsive">
                        <table id="users_table" class="table table-bordered table-hover nowrap text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name </th>
                                    <th>Email </th>
                                    <th>Roles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                           <tbody>
                            @foreach($list as $lists)
                                <tr>
                                    <td>{{$lists->name}}</td>
                                    <td>{{$lists->email}}</td>
                                    <td>{{implode(', ',$lists->roles->pluck('name')->toArray())}}</td>
                                    <td>
                                 <!-- <form action="{{ url('/users/'.$lists->id) }}" method="POST"> -->
                                    <form action="{{ url('/users')}}/{{$lists->id}}" method="POST" class="d-md-flex justify-content-md-between">
                                    @csrf
                                    @method('PUT')
                                        <div class="d-md-flex pr-2 align-items-center">
                                        @foreach($role as $roles)
                                            <div class="form-check ml-2">
                                            <input name="role_id[]" class="form-check-input" type="checkbox" value="{{$roles->id}}" id="flexCheckboxDefault" 
                                            @if (in_array($roles->id,$lists->roles->pluck('id')->toArray()))
                                                checked
                                            @endif

                                            <label class="form-check-label" for="flexCheckboxDefault">
                                                {{$roles->name}}
                                            </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        </form>
                                    </td>
                                </tr> 
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop


@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>

    <script>
        new DataTable('#users_table', {
            responsive: true,
            stateSave: true,
            columnControl: true,
            ordering: true,
            initComplete: function () {
                document.querySelector('#users_table').classList.remove('opacity-0');
            }
        });

    </script>
@stop
