<!-- footer strt -->
<footer class="position-top bg_img pb-70" data-background="assets/img/bg/footer_bg.png">
    <div class="container">
        <div id="contact" class="contact pb-100">
            <div class="row">
                <div class="col-lg-7">
                    <div class="xb-contact contact-mt--255">
                        <div class="contact-title mb-35">
                            <span><img src="assets/img/icon/directbox-notif.svg" alt="">Contact Us</span>
                            <h3>Do you have questions or want more <br> information?</h3>
                        </div>
                        <form id="contact_form" class="contact-from" action="{{route('contact.send')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="xb-item--field">
                                        <span><img src="assets/img/icon/c_user.svg" alt=""></span>
                                        <input type="text" placeholder="Name" name="name">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="xb-item--field">
                                        <span><img src="assets/img/icon/c_mail.svg" alt=""></span>
                                        <input type="text" placeholder="Email" name="email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="xb-item--field">
                                        <span><img src="assets/img/icon/c_call.svg" alt=""></span>
                                        <input type="text" placeholder="phone" name="phone">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="xb-item--field">
                                        <span><img src="assets/img/icon/c_message.svg" alt=""></span>
                                        <textarea name="message" id="message" cols="30" rows="10"
                                                  placeholder="Write Your Message..." ></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="thm-btn thm-btn--black" type="submit">Send Message</button>
                                </div>
                            </div>
                            <!-- Success Alert -->
                            <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                                <strong>Success!</strong>Message sent successfully.
                            </div>
                            <!-- Error Alert -->
                            <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                                <strong>Error!</strong> Failed to send your message. Please try again.
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="contact-info contact-mt--255 mt-md-30">
                        <div class="xb-item--head">
                            <div class="xb-item--address">
                                <h3><img src="assets/img/icon/location.svg" alt="">our address</h3>
                                <p>100 Orchard st, New <br> York,NY 100025 USA</p>
                            </div>
                            <div class="xb-item--open">
                                <p>Monday - Friday <br>
                                    08:00AM - 05:00PM</p>
                                <a href="mailto:purefit@gmail.com"><img src="assets/img/icon/sms-tracking.svg" alt="">purefit@gmail.com</a>
                            </div>
                            <ul class="xb-item--social ul_li mt-30">
                                <li><a href="#!"><i class="fab fa-telegram-plane"></i></a></li>
                                <li><a href="https://api.whatsapp.com/send?phone=96181823038" target="_blank"><i class="fab fa-whatsapp"></i></a></li>
                                <li><a href="https://www.instagram.com/supplement_station_lb" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            </ul>
                        </div>
                        <div class="xb-item--cta" data-background="assets/img/bg/cta_bg.jpg">
                            <p>Our help desk is a vailable for you <br> every day, 07:00AM - 10:00PM</p>
                            <h3>+961 81-823-038</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-inner">
            <div class="footer-logo mb-25 text-center">
                <img src="assets/img/logo/f_logo.png" alt="">
            </div>
            <div class="sec-title sec-title--white text-center mb-50">
                <h2 class="title">in a healthy body, healthy mind</h2>
            </div>
            <ul class="footer-nav ul_li_center">
                <li><a href="#!">all products</a></li>
                <li><a href="#!">track order</a></li>
                <li><a href="#!">my account</a></li>
                <li><a href="#!">gift cards</a></li>
                <li><a href="#!">our story</a></li>
                <li><a href="#!">careers</a></li>
                <li><a href="#!">contact</a></li>
            </ul>
            <div class="footer-bottom mt-50 ul_li_between">
                <div class="footer-copyright mt-30">
                    Copyright © 2024 purefit All rights reserved.
                </div>
                <ul class="footer-links ul_li mt-30">
                    <li><a href="#!">terms of conditions</a></li>
                    <li><a href="#!">privacy pllicy</a></li>

                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#contact_form').submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = $(this).serialize();

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                success: function(response) {
                    $('#successAlert').fadeIn();
                    setTimeout(function() {
                        $('#successAlert').fadeOut();
                    }, 3000);
                    // alert('email sent!');
                },
                error: function(xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    alert(error);
                    setTimeout(function() {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                    // alert('error');
                }
            });
        });
    });
</script>
<!-- footer end -->
