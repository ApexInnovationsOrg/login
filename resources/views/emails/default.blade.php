<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <!-- **************************************************************************************** -->
  <!-- IF YOU ARE HAVING PROBLEMS VIEWING THIS THEN YOU PROBABLY CANNOT RECEIVE HTML EMAILS. WE -->
  <!-- ALSO SEND TEXT-ONLY VERSIONS. TO CHANGE YOUR PREFERED EMAIL FORMAT PLEASE SEND EMAIL TO  -->
  <!-- support@apexinnovations.com OR LOGIN TO http://www.apexinnovations.com TO MODIFY YOUR PROFILE. -->
  <!-- **************************************************************************************** -->
  <head>
    <meta http-equiv="Content-Language" content="en-us">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>$subject</title>
    <style type="text/css">
	table{ font-family: Verdana; }
    </style>
  </head>
  <body>
    <table style="width: 75%;">
      <tr>
        <td colspan="2">
          <center><img src="<?php echo $message->embed('https://www.apexinnovations.com/post_office/image001.jpg'); ?>" style="padding-bottom: 10px;" /></center>
        </td>
      </tr>
      <tr>    
        <td colspan="2" style="border-top: 4px solid #990000; margin-bottom: 25px; height: 0px;"></td>
      </tr>  
      <tr>
        @yield('content')
      </tr>
      <tr>
        <td colspan="2">  
          <p>Please save this e-mail for future reference!</p>
          <p>Sincerely,</p>
          <p>The Apex Innovations Team</p>
          <p style="color: #999999;">Have questions? Please don't reply to this e-mail as we cannot respond to messages sent to this address. Instead, visit our <a href="https://www.apexinnovations.com/faq.html">FAQ Page</a> for answers to the most commonly asked questions, <a href="mailto:support@apexinnovations.com">send us a message</a>, or call us at 866-294-4599.</p>
          <p style="font-size: 0.9em;">
            Follow Us!
            <a href="https://www.facebook.com/Apex.online.continuing.education"><img src="<?php echo $message->embed('https://www.apexinnovations.com/post_office/image002.png'); ?>"/></a>
            <a href="https://twitter.com/ApexInnovations"><img src="<?php echo $message->embed('https://www.apexinnovations.com/post_office/image004.png'); ?>"/></a>
            <a href="http://www.linkedin.com/company/737013"><img src="<?php echo $message->embed('https://www.apexinnovations.com/post_office/image006.png'); ?>"/></a>
            <a href="http://www.youtube.com/user/TheApexInnovations"><img src="<?php echo $message->embed('https://www.apexinnovations.com/post_office/image008s.png'); ?>"/></a>
          </p>
        </td>
      </tr>
    </table>   
  </body>
</html>