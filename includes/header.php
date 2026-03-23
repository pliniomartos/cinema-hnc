<?php
// Ensure session is started before any output is made
if (session_id() == '') {
	session_start();
}

// Check if the cookies policy modal has already been shown
if (!isset($_SESSION['cookies_policy_shown'])) {
	$_SESSION['cookies_policy_shown'] = true;
	$show_modal = true;
} else {
	$show_modal = false;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ec Cinema</title>

    <!-- Include Bootstrap and other resources -->
    <script src="/cinema/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/cinema/script.js"></script>
    <link href="/cinema/scss/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">

    <!--Useberry Tracking code-->
    <script type="text/javascript" src="https://api.useberry.com/integrations/liveUrl/scripts/useberryScript.js"></script>

    <!--Maze Tracking code-->
    <script>
        (function (m, a, z, e) {
            var s, t;
            try {
                t = m.sessionStorage.getItem('maze-us');
            } catch (err) {}

            if (!t) {
                t = new Date().getTime();
                try {
                    m.sessionStorage.setItem('maze-us', t);
                } catch (err) {}
            }

            s = a.createElement('script');
            s.src = z + '?apiKey=' + e;
            s.async = true;
            a.getElementsByTagName('head')[0].appendChild(s);
            m.mazeUniversalSnippetApiKey = e;
        })(window, document, 'https://snippet.maze.co/maze-universal-loader.js', 'fe10f934-a4dc-4e24-9458-3ed3299c2f7c');
    </script>


    <!-- Hotjar Tracking Code -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:5225367,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check if the modal element exists before proceeding
            var cookiesPolicyModalHeaderElement = document.getElementById('cookiesPolicyModalHeader');
            if (cookiesPolicyModalHeaderElement) {
                // Create a new Bootstrap modal instance if $show_modal is true
                var cookiesPolicyModalHeader = new bootstrap.Modal(cookiesPolicyModalHeaderElement);

							<?php if ($show_modal): ?>
                cookiesPolicyModalHeader.show();
							<?php endif; ?>

                // Add event listeners for the modal buttons
                var acceptCookiesButton = document.getElementById('acceptCookies');
                if (acceptCookiesButton) {
                    acceptCookiesButton.addEventListener('click', function () {
                        cookiesPolicyModalHeader.hide();
                    });
                }

                var rejectCookiesButton = document.getElementById('rejectCookies');
                if (rejectCookiesButton) {
                    rejectCookiesButton.addEventListener('click', function () {
                        window.location.href = 'about:home';
                    });
                }
            }
        });
    </script>
</head>
<body>

<!-- Modal HTML -->
<div class="modal fade" id="cookiesPolicyModalHeader" tabindex="-1" aria-labelledby="cookiesPolicyModalHeaderLabel" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white">
            <div class="modal-header text-orange0">
                <h3 class="modal-title" id="cookiesPolicyModalHeaderLabel">Cookies Policy</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>We use cookies to ensure you get the best experience on our website. Do you accept our cookies policy?</p>
            </div>
            <div class="modal-footer">
                <a href="/cinema/home.php" button" id="acceptCookies" class="btn btn-outline-orange0">Accept</a>
                <a href="javascript:window.close();" role="button" id="rejectCookies" class="btn btn-outline-red1">Reject</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
