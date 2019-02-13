@extends('app')
@section('modals')
<div class="modal fade" id="authy-modal">
  <div class='modal-dialog'>
    <div class="modal-content">
      <div class='modal-header'>
        <h4 class='modal-title'>Please Authenticate</h4>
      </div>
      <div class='modal-body auth-ot'>
        <div class='help-block'>
          <i class="fa fa-spinner fa-pulse"></i> Waiting for OneTouch Approval, check your phone ...
        </div>
      </div>
      <div class='modal-body auth-token'>
        <div class='help-block'>
          <i class="fa fa-mobile"></i> Authy OneTouch not available
        </div>
        <p>Please enter your Token</p>
        <form id="authy-sms-form" class="form-horizontal" role="form" method="POST" action="/auth/twofactor">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <div class='form-group'>
            <label class="col-md-4 control-label" for="token">Authy Token</label>
            <div class='col-md-6'>
              <input type="text" name="token" id="authy-token" ng-model="token" value="" class="form-control" autocomplete="off" />
            </div>
          </div>
          <a value="Verify" class="btn btn-default" href="#" ng-click="cancel()">Cancel</a>
          <input type="submit" name="commit" value="Verify2" class="btn btn-success" ng-click="verifyToken(token)" />
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <div class="panel panel-default">
        <div class="panel-heading">Login</div>
        <div class="panel-body">
          @if (count($errors) > 0)
            <div class="alert alert-danger">
              <strong>Whoops!</strong> There were some problems with your input.<br><br>
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div  id="ajax-error" class="alert alert-danger hidden"></div>
          <form id="login-form" class="form-horizontal" role="form" method="POST" action="/auth/login">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group">
              <label class="col-md-4 control-label">E-Mail Address</label>
              <div class="col-md-6">
                <input type="email" class="form-control" name="email" value="{{ old('email') }}">
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-4 control-label">Password</label>
              <div class="col-md-6">
                <input type="password" class="form-control" name="password">
              </div>
            </div>

            <div class="form-group">
              <div class="col-md-6 col-md-offset-4">
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="remember"> Remember Me
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary" style="margin-right: 15px;">
                  Login
                </button>

                <a href="/password/email">Forgot Your Password?</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script src="/js/sessions.js" type="text/javascript"></script>
@endsection
