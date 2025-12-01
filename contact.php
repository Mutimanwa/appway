<?php include 'includes/header.php'; ?>

<!-- page-title -->
<section class="page-title" style="background-image: url(assets/images/background/pagetitle-bg.png);">
    <div class="anim-icons">
        <div class="icon icon-1"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-17.png" alt=""></div>
        <div class="icon icon-2 rotate-me"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-18.png" alt=""></div>
        <div class="icon icon-3 rotate-me"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-19.png" alt=""></div>
        <div class="icon icon-4"></div>
    </div>
    <div class="container">
        <div class="content-box clearfix">
            <div class="title-box pull-left">
                <h1>Contact Us</h1>
                <p>Reach out to the world’s most reliable IT services.</p>
            </div>
            <ul class="bread-crumb pull-right">
                <li>Contact Us</li>
                <li><a href="index.html">Home</a></li>
            </ul>
        </div>
    </div>
</section>
<!-- page-title end -->


<!-- contact-section -->
<section class="contact-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-md-12 col-sm-12 offset-lg-1 big-column">
                <div class="info-content centred">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12 info-column">
                            <div class="single-info-box">
                                <figure class="icon-box"><img src="<?= IMAGES_URL; ?>/icons/info-icon-1.png" alt="">
                                </figure>
                                <h2>Phone</h2>
                                <div class="text">Travailler en équipe soudée. Apprendre constamment. Partager nos
                                    connaissances. </div>
                                <div class="phone"><a href="tel:+25761103320">+257 61 103 320</a></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 info-column">
                            <div class="single-info-box">
                                <figure class="icon-box"><img src="<?= IMAGES_URL; ?>/icons/info-icon-2.png" alt="">
                                </figure>
                                <h2>E-mail</h2>
                                <div class="text">Travailler en équipe soudée. Apprendre constamment. Partager nos
                                    connaissances.</div>
                                <div class="phone"><a
                                        href="mailto:plccreativeroom7@gmail.com">plccreativeroom7@gmail.com</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 info-column">
                            <div class="single-info-box">
                                <figure class="icon-box"><img src="<?= IMAGES_URL; ?>/icons/info-icon-3.png" alt="">
                                </figure>
                                <h2>Address</h2>
                                <div class="text">Bujumbura, Burundi</div>
                                <!-- <div class="phone"><a href="#">View on Google map</a></div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="image-container">
                    <figure class="image-box"><img src="<?= IMAGES_URL; ?>/resource/contact-1.png" alt="">
                    </figure>
                </div>
                <div class="contact-form-area">
                    <div class="sec-title center">
                        <h2>Contact us</h2>
                    </div>
                    <div class="form-inner">
                        <form method="post" action="sendemail.php" id="contact-form" class="default-form">
                            <div class="row">
                                <div class="col-lg-6 col-md-12 col-sm-12 column">
                                    <div class="form-group">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="username" placeholder="Name" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12 column">
                                    <div class="form-group">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12 column">
                                    <div class="form-group">
                                        <i class="fas fa-file"></i>
                                        <input type="text" name="subject" placeholder="Subject" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12 column">
                                    <div class="form-group">
                                        <i class="fas fa-phone"></i>
                                        <input type="text" name="phone" placeholder="Phone" required>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 column">
                                    <div class="form-group">
                                        <textarea name="message" placeholder="Write here message"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 column">
                                    <div class="form-group message-btn centred">
                                        <button type="submit" class="theme-btn-two" name="submit-form">Submit
                                            Now</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- contact-section end -->


<!-- map-section -->
<section class="map-section">
    <div class="google-map-area">
        <div class="google-map" id="contact-google-map" data-map-lat="-3.361378" data-map-lng="29.359878"
            data-icon-path="images/icons/map-marker.png" data-map-title="Bujumbura, Burundi" data-map-zoom="12"
            data-markers='{
                    "marker-1": [-3.361378, 29.359878, "<h4>Siège Principal</h4><p>Bujumbura, Burundi</p>", "images/icons/map-marker.png"],
                    "marker-2": [-3.426186, 29.932248, "<h4>Antenne Gitega</h4><p>Gitega, Capitale politique</p>", "images/icons/map-marker.png"]
                }'>
        </div>
    </div>
</section>
<!-- map-section end -->




<!-- map script -->
<script src="http://maps.google.com/maps/api/js?key=AIzaSyATY4Rxc8jNvDpsK8ZetC7JyN4PFVYGCGM"></script>
<script src="js/gmaps.js"></script>
<script src="js/map-helper.js"></script>

<?php include 'includes/footer.php'; ?>