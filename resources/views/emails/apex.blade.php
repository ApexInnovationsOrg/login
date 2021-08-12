@component('mail::message')
# Introduction

Looks like you forgot your password... No problem! Just click below, and we'll get you on your way :)

P.S. - This link expires in 60 minutes!

@component('mail::button', ['url' => ''])
Reset your password ->
@endcomponent


Sincerely,<br>
The Apex Innovations Team
@endcomponent
