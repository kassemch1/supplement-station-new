<!-- footer strt -->
<footer class="position-top bg_img pb-70" data-background="{{ asset('assets/img/bg/footer_bg2.jpg')}}">
    <div class="container">
        <div id="contact" class="contact pb-100">
            <div class="row">
                <div class="col-lg-7">
                    <div class="xb-contact contact-mt--255">
                        <div class="contact-title mb-35">
                            <span><img src="{{ asset('assets/img/icon/directbox-notif.svg') }}" alt="">Contact Us</span>
                            <h3>Do you have questions or want more <br> information?</h3>
                        </div>
                        <form id="contact_form" class="contact-from" action="{{route('contact.send')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="xb-item--field">
                                        <span><img src="{{ asset('assets/img/icon/c_user.svg')}}" alt=""></span>
                                        <input type="text" placeholder="Name" name="name">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="xb-item--field">
                                        <span><img src="{{ asset('assets/img/icon/c_mail.svg')}}" alt=""></span>
                                        <input type="text" placeholder="Email" name="email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="xb-item--field">
                                        <span><img src="{{ asset('assets/img/icon/c_call.svg')}}" alt=""></span>
                                        <input type="text" placeholder="phone" name="phone">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="xb-item--field">
                                        <span><img src="{{ asset('assets/img/icon/c_message.svg')}}" alt=""></span>
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
                                <h3><img src="{{ asset('assets/img/icon/location.svg') }}" alt="">our address</h3>
                                <p>Msharafieh Facing Garden State<br> Beirut, Lebanon</p>
                            </div>
                            <div class="xb-item--open">
                                <p>Our customer support is available Monday to Saturday, 9:00 AM – 9:00 PM.<br>Our website is available 24/7 for your convenience.</p>
                                <a href="mailto:admin@supplement-station.com"><img src="{{ asset('assets/img/icon/sms-tracking.svg') }}" alt="">admin@supplement-station.com</a>
                            </div>
                            <ul class="xb-item--social ul_li mt-30">

                                <li><a href="https://api.whatsapp.com/send?phone=96181823038" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                                <li><a href="https://www.instagram.com/supplement_station_lb" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                            </ul>
                        </div>
                        <div class="xb-item--cta" data-background="{{ asset('assets/img/bg/cta_bg2.jpg') }}">
                            <p>we are available for you <br> Monday to Saturday, 09:00AM - 9:00PM</p>
                            <h3><a href="tel:+96181823038" style="color: inherit">+961 81-823-038</a> / <a href="tel:+96181088266" style="color: inherit">81-088-266</a></h3>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-inner">
            <div class="footer-logo mb-25 text-center">
                <img src="{{ asset('assets/img/logo/supLogo.png') }}" alt="Supplement Station" style="max-height: 120px; max-width: 120px;" class="logo" loading="lazy">
            </div>
            <div class="sec-title sec-title--white text-center mb-50">
                <h2 class="title">BREAK YOUR LIMITS AT SUPPLEMENT STATION</h2>
            </div>
            <ul class="footer-nav ul_li_center">
                <li><a href="{{ route('shop') }}">all products</a></li>
                <li><a href="{{ route('home') }}#pricing">plans</a></li>
                <li><a href="{{ route('home') }}#contact">contact</a></li>
                <li><a href="{{ route('cart') }}">cart</a></li>
                <li><a href="{{ route('checkout') }}">checkout</a></li>
            </ul>
            <div class="footer-bottom mt-50 d-flex justify-content-center align-items-center">
                <div class="footer-copyright mt-30 text-center">
                    <span class="item-content">
                        &copy; {{ date('Y') }} Supplement Station, Owned by Amine el Sayed. All rights reserved.<br> Powered by <a href="https://www.tawwer.tech/" target="_blank">Tawwer</a>.
                    </span>
                </div>
            </div>

        </div>
    </div>
</footer>

@include('partials/recentPurchaseToast')

<!-- footer end -->
