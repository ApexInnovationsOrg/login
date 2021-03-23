



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
  <p style="font-size:20px">Hi {{ $user->FirstName . ' '. $user->LastName}},</p>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Looks like you forgot your password... No problem! Just click below, and we'll get you on your way :)</div>
  <div>P.S. - This link expires in 60 minutes!</div>
  <br/>
  <table border="1" cellspacing="0" cellpadding="0" style="background-color:#337ab7;border-style:none none solid none;border-bottom-width:1.5pt;border-bottom-color:#2e6da4; text-align:center;">
    <tbody>
        <tr>
            <td style="padding:0;border-style:none;">
                <div style="margin:0;">
                </div>
            </td>
            <td style="padding:0 17.25pt 1.5pt 18pt;border-style:none;">
                <div align="center" style="text-align:center;margin:10px;">
                    <font face="Times New Roman,serif" size="3"><span style="font-size:12pt;"><a href={{ url('password/reset/'.$token) }} target="_blank" style="text-decoration:none; color:#ffffff"><font face="Segoe UI,sans-serif" size="4"><span style="font-size:13.5pt;"><font size="4" color="white"><span style="font-size:15pt;" style="color:#ffffff !important">Reset
your password&nbsp;&nbsp;></span></font>
                    </span>
                    </font>
                    </a>
                    </span>
                    </font>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
</td>
@endsection

