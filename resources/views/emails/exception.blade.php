@extends('emails.default')

@section('content')
<td colspan="2"  style="margin-top:15px; padding:40.5pt 40.5pt 0 40.5pt;">
<style>
a:visited{
    color:#ffffff;
}
a{
    text-decoration:none;
}
</style>
  <p style="font-size:20px">Developers,</p>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Something didn't work with the login system</div>
  <p><strong>ERROR:</strong><br/>{{ $error }}</p>
  <p><strong>URL:</strong><br/>{{ $caller }}</p>
  <p><strong>Backtrace:</strong><br/>{{ $backtrace }}</p>
  <p><strong>GET:</strong><br/>{{ $get }}</p>
  <p><strong>POST:</strong><br/>{{ $post }}</p>
  <p><strong>SESSION:</strong><br/>{{ $session }}</p>
  <p><strong>Remote IP Address:</strong><br/>{{ $_SERVER['REMOTE_ADDR'] }}</p>
  <p><strong>Rendering Server:</strong><br/>{{ gethostname() }} - {{ $_SERVER['SERVER_SOFTWARE'] }} :: PHP {{ phpversion() }}</p>
  <table border="1" cellspacing="0" cellpadding="0" style="background-color:#337ab7;border-style:none none solid none;border-bottom-width:1.5pt;border-bottom-color:#2e6da4; text-align:center;">
    <tbody>
        <tr>
            <td style="padding:0;border-style:none;">
                <div style="margin:0;">
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
</td>
@endsection

