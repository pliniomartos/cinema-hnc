
<script>
    function showUser(str) {
        if (str == "") {
            document.getElementById("txtHint").innerHTML = "";
            return;
        } else {
            var xmlhttp;
            if (window.XMLHttpRequest) {
                xmlhttp = new XMLHttpRequest();
            } else {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getmovie.php?q=" + str, true);
            xmlhttp.send();
        }
    }
</script>

<nav class="navbar navbar-expand-md navbar-dark bg-transparent ms-md-5 me-md-5 px-3 rounded-bottom-5 border-5 border-bottom border-yellow-1">
    <a class="navbar-brand text-orange0" href="#">Select a Movie</a>
    <form class="form-inline my-lg-0" data-bs-theme="dark">
        <select class="form-control bg-black" id="exampleFormControlSelect2" name="users"
                onchange="showUser(this.value)">
            <option value="">Select Movie:</option>
					<?php
					# Open database connection.
					require 'connect_db.php';
					# Retrieve movies from 'movie_listings' database table.
					$q = "SELECT * FROM movie_listings ORDER BY `release` DESC";
					$r = mysqli_query($link, $q);
					if (mysqli_num_rows($r)) {
						while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
							echo '<option value="' . $row['movie_id'] . '">' . $row['movie_title'] . '</option>';
						}

					}
					?>
        </select>
    </form>
</nav>

<div class="container my-5 sticky-top " style="width: fit-content;"><h1 class="text-center text-orange0">Home</h1></div>
<div class="container">
    <div id="txtHint">
        <!-- Display Search -->
    </div>
</div>