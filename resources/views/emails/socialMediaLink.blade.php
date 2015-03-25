@extends('emails.default')

@section('content')
<td colspan="2">
  <p>Hello {{ $name }},</p>
  <p>Click here to link your {{ $provider }} account: <a href="{{ url('auth/Social/link/?data=') . $encryptedLink }}">join accounts</a></p>
  <p>Note: this link expires after 30 minutes!</p>
</td>
@endsection






