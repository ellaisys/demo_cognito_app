@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <img src="https://github.com/ellaisys/aws-cognito/raw/master/assets/images/banner.png" 
                        width="100%" alt="EllaiSys AWS Cloud Capability"/>

                    <h2><strong>Welcome: {{ __('You are logged in!') }}</strong></h2>
                    <h4>This is a demo application, that uses the Laravel Package to manage Web and API authentication with AWS Cognito</h4>
                
                    </br>
                    <h2><strong>Session Parameters:</strong></h2>
                    @if ($sessionData = session()->all())
                        <table class="table table-bordered table-striped">
                            <thead class="dark">
                                <tr>
                                    <td style="width: 30%;">Key</td>
                                    <td>Value</td>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($sessionData as $key=>$value)
                                <tr>
                                    <td style="word-break: break-word;">{{ $key }}</td>
                                    <td style="word-break: break-word;">{{ json_encode($value, JSON_UNESCAPED_UNICODE)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

    @if ($actionActivateMFA = session()->get('actionActivateMFA'))
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" id="modalMFAActivate" aria-labelledby="modalMFAActivate" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form name="verify-mfa-code-form" id="verify-mfa-code-form" method="post" action="{{route('cognito.action.mfa.verify')}}" autocomplete="off">
                    @csrf

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Activation QR Code</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="flex-fill font-regular text-nowrap">
                                <small>Key: {{ $actionActivateMFA['SecretCode'] }}</small>
                                <input type="hidden" name="mfa_secret_code" id="mfa_secret_code"
                                value="{ $actionActivateMFA['SecretCode'] }}" />
                            </div>
                            <div class="flex-fill">
                                <img src="{{ $actionActivateMFA['SecretCodeQR'] }}" class="mx-auto d-block img-thumbnail" />
                            </div>
                            <div class="flex-fill">
                                <a href="{{ $actionActivateMFA['TotpUri'] }}" target="_blank">TOTP Link</a>
                            </div>

                            <div class="flex-fill form-group mt-4 w-50">
                                <input type="text" name="code" id="code" class="form-control"  placeholder="Enter the code" pattern="[0-9]{6}" required autofocus autocomplete="off" maxlength="6"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" tabindex="1" />
                            </div>
                            <div class="flex-fill form-group mt-1 w-50">
                                <input type="text" name="device_name" id="device_name"
                                value="My Phone" class="form-control" required placeholder="Enter the device name" autocomplete="off" tabindex="2" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                    </div>                    
                </form>
            </div>
        </div>

        <script type="text/javascript">
            var modalMFAActivate = new bootstrap.Modal(document.getElementById('modalMFAActivate'), {keyboard: false});
            modalMFAActivate.show();
        </script>
    @endif
@endsection
