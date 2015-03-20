		(function() {
		    if (typeof window.janrain !== 'object') window.janrain = {};
		    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
		    
		    /* _______________ can edit below this line _______________ */

		    janrain.settings.tokenUrl = 'http://' + document.location.hostname + '/auth/Social';
		    janrain.settings.type = 'embed';
		    janrain.settings.appId = 'indelmfedkggifiaagpp';
		    janrain.settings.appUrl = 'https://apexinnovations.rpxnow.com';
		    janrain.settings.providers = ["yahoo","facebook","microsoftaccount","googleplus"];
		    janrain.settings.providersPerPage = '6';
		    janrain.settings.format = 'two column';
		    janrain.settings.actionText = 'Or sign in using your account with';
		    janrain.settings.showAttribution = true;
		    janrain.settings.fontColor = '#333333';
		    janrain.settings.fontFamily = '"Helvetica Neue",Helvetica,Arial,sans-serif';
		    janrain.settings.backgroundColor = '#FFFFFF';
		    janrain.settings.width = '380';
		    janrain.settings.borderColor = '#FFFFFF';
		    janrain.settings.borderRadius = '10';    janrain.settings.buttonBorderColor = '#CCCCCC';
		    janrain.settings.buttonBorderRadius = '5';
		    janrain.settings.buttonBackgroundStyle = 'gradient';
		    janrain.settings.language = '';
		    janrain.settings.linkClass = 'janrainEngage';

		    /* _______________ can edit above this line _______________ */

		    function isReady() { janrain.ready = true; };
		    if (document.addEventListener) {
		      document.addEventListener("DOMContentLoaded", isReady, false);
		    } else {
		      window.attachEvent('onload', isReady);
		    }

		    var e = document.createElement('script');
		    e.type = 'text/javascript';
		    e.id = 'janrainAuthWidget';

		    if (document.location.protocol === 'https:') {
		      e.src = 'https://rpxnow.com/js/lib/apexinnovations/engage.js';
		    } else {
		      e.src = 'http://widget-cdn.rpxnow.com/js/lib/apexinnovations/engage.js';
		    }

		    var s = document.getElementsByTagName('script')[0];
		    s.parentNode.insertBefore(e, s);

		    
		})();

		$(document).ready(function(){
			$('#registerNewAcct').click(function(){
				$(this).slideUp();
				$('#licensePrompt').slideDown();
			});
			$('.btn-group button').click(function(){
				$("[name='hasLicense']").val($(this).data('value'));
				$('.btn-group button').eq(0).parents('form').submit()
			})

		});

//google analytics
  // (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  // (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  // m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  // })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  // ga('create', 'UA-60099844-1', 'auto');
  // ga('send', 'pageview');