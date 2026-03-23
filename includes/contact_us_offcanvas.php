<!-- Contact Us Offcanvas -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="offcanvas offcanvas-end text-white bg-black border-2 border-start border-orange0" data-bs-scroll="true" tabindex="-1" id="contactUsCanvas" aria-labelledby="contactUsCanvasLabel" data-bs-theme="dark">
 <div class="offcanvas-header">
  <h3 class="offcanvas-title text-orange0" id="contactUsCanvasLabel">Contact Us</h3>
  <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
 </div>
 <div class="offcanvas-body">
  <form id="contactForm" class="was-validated">
      <div class="mb-3">
          <label for="fullName" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullName" name="fullName" required>
      </div>
      <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" class="form-control" id="phone" name="phone" required>
      </div>
      <div class="mb-3">
          <label for="description" class="form-label">Reason for Contacting Us</label>
          <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-outline-orange0">Submit</button>
  </form>
 </div>
</div>

<!-- Thank You Modal -->
<div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-black text-orange0">
      <div class="modal-header">
        <h5 class="modal-title" id="thankYouModalLabel">Thank You!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-white">Thank you for contacting us. We have received your message and will get back to you shortly.</p>
      </div>
      <div class="modal-footer">
        <a href="home.php" class="btn btn-outline-orange0">Return to Home</a>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#contactForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: 'includes/process_contact_form.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var thankYouModal = new bootstrap.Modal(document.getElementById('thankYouModal'));
                thankYouModal.show();
            },
            error: function(xhr, status, error) {
                console.error('Form submission failed:', error);
            }
        });
    });
});
</script>