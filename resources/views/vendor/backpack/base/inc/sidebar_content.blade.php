<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('survey') }}'><i class='nav-icon la la-poll'></i> Surveys</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('template') }}'><i class='nav-icon la la-poll'></i>Templates</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('custom-form-data') }}'><i class='nav-icon la la-poll'></i> Custom Form Data</a></li>
@if(backpack_user()->is_admin)
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user') }}'><i class='nav-icon la la-user'></i> Users</a></li>
@endif
