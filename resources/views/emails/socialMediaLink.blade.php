Hello {{ $name }}, 
<br/>
Click here to link your {{ $provider }} account: <a href="{{ url('auth/Social/link/?data=') . $encryptedLink }}">join accounts</a>




