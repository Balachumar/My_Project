<link rel="stylesheet" href="style.css">
<section class="page">
    <div class="hero">
        <h2>Welcome to Elegance Salon</h2>
        <p>Experience luxury and style with our premium beauty and wellness services. Our expert team is dedicated to making you look and feel your best.</p>
        <a href="?page=appointments" class="btn btn-primary">Book an Appointment</a>
    </div>
    
    <div class="services">
        <?php
        $sql = "SELECT * FROM services LIMIT 4";
        $result = $conn->query($sql);
        
        $icons = [
            'Hair Services' => 'fa-cut',
            'Nail Services' => 'fa-hand-sparkles',
            'Skin Care' => 'fa-spa',
            'Makeup' => 'fa-palette'
        ];
        
        while ($service = $result->fetch_assoc()):
            $icon = isset($icons[$service['category']]) ? $icons[$service['category']] : 'fa-star';
        ?>
        <div class="service-card">
            <div class="service-img">
                <i class="fas <?php echo $icon; ?>"></i>
            </div>
            <div class="service-content">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

            
    <section class="about" id="about">
        <div class="container">
            <div class="about-container">
                <div class="about-content">
                    <h2>About Elegance Salon</h2>
                    <p>For over a decade, Elegance Salon has been the premier destination for those seeking exceptional beauty and wellness services in a luxurious environment.</p>
                    <p>Our team of certified professionals stays current with the latest trends and techniques to ensure you receive the highest quality service.</p>
                    
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Expert Stylists</h4>
                                <p>Our team consists of highly trained and experienced beauty professionals.</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Premium Products</h4>
                                <p>We use only the highest quality, professional-grade products in all our services.</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Luxurious Environment</h4>
                                <p>Relax and unwind in our beautifully designed, comfortable salon space.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-img">
                    <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80" alt="Elegance Salon Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Our Salon</h2>
                <p>Take a look at our beautiful, luxurious salon environment designed for your comfort and relaxation</p>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="salon interior.jpg">
                    <div class="gallery-overlay">
                        <h3>Relaxing Atmosphere</h3>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="Styling Stations.jpg">
                    <div class="gallery-overlay">
                        <h3>Professional Styling Stations</h3>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="Spa areas.jpg" alt="Spa Area">
                    <div class="gallery-overlay">
                        <h3>Luxurious Spa Area</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="services-overview" id="services">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>We offer a comprehensive range of premium beauty and wellness services</p>
            </div>
            <div class="services-container">
                <div class="service-category">
                    <h3><i class="fas fa-cut"></i> Hair Services</h3>
                    <ul class="service-list">
                        <li><span>Haircut & Styling</span><span>$65+</span></li>
                        <li><span>Hair Coloring</span><span>$85+</span></li>
                        <li><span>Balayage & Highlights</span><span>$120+</span></li>
                        <li><span>Keratin Treatment</span><span>$150+</span></li>
                        <li><span>Extensions</span><span>$200+</span></li>
                    </ul>
                </div>
                <div class="service-category">
                    <h3><i class="fas fa-spa"></i> Skin & Beauty</h3>
                    <ul class="service-list">
                        <li><span>Facials</span><span>$80+</span></li>
                        <li><span>Microdermabrasion</span><span>$120+</span></li>
                        <li><span>Chemical Peels</span><span>$150+</span></li>
                        <li><span>Lash Extensions</span><span>$120+</span></li>
                        <li><span>Brow Shaping</span><span>$35+</span></li>
                    </ul>
                </div>
                <div class="service-category">
                    <h3><i class="fas fa-hand-sparkles"></i> Nail Services</h3>
                    <ul class="service-list">
                        <li><span>Classic Manicure</span><span>$35+</span></li>
                        <li><span>Gel Manicure</span><span>$50+</span></li>
                        <li><span>Spa Pedicure</span><span>$60+</span></li>
                        <li><span>Nail Art</span><span>$15+</span></li>
                        <li><span>Nail Repair</span><span>$20+</span></li>
                    </ul>
                </div>
                <div class="service-category">
                    <h3><i class="fas fa-palette"></i> Makeup & Special Events</h3>
                    <ul class="service-list">
                        <li><span>Everyday Makeup</span><span>$65+</span></li>
                        <li><span>Bridal Makeup</span><span>$150+</span></li>
                        <li><span>Special Occasion</span><span>$90+</span></li>
                        <li><span>Makeup Lesson</span><span>$100+</span></li>
                        <li><span>Group Bookings</span><span>Contact Us</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Client Testimonials</h2>
                <p>See what our clients have to say about their experience at Elegance Salon</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "I've been coming to Elegance Salon for over 5 years and wouldn't trust anyone else with my hair. The team is incredibly talented and the atmosphere is so relaxing."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-img">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                        </div>
                        <div class="author-info">
                            <h4>Sarah Johnson</h4>
                            <p>Regular Client</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "The bridal package was worth every penny! My makeup stayed flawless all day and I received so many compliments. Thank you for making me feel so beautiful on my wedding day."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-img">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emily Roberts">
                        </div>
                        <div class="author-info">
                            <h4>Emily Roberts</h4>
                            <p>Bride</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "As a busy professional, I appreciate how efficient yet thorough the services are. My keratin treatment has saved me so much time in my morning routine. Highly recommend!"
                    </div>
                    <div class="testimonial-author">
                        <div class="author-img">
                            <img src="https://randomuser.me/api/portraits/women/26.jpg" alt="Michelle Chen">
                        </div>
                        <div class="author-info">
                            <h4>Michelle Chen</h4>
                            <p>Corporate Client</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
         /* About Section */
    .about {
            padding: 100px 0;
        }

        .about-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-img {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .about-img img {
            width: 100%;
            height: auto;
            display: block;
            transition: var(--transition);
        }

        .about-img:hover img {
            transform: scale(1.05);
        }

        .about-content h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .about-content p {
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .features {
            margin-top: 30px;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .feature i {
            color: var(--primary);
            font-size: 1.2rem;
            margin-right: 15px;
            margin-top: 5px;
        }

        /* Gallery Section */
        .gallery {
            padding: 100px 0;
            background-color: var(--secondary);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent);
        }

        .section-title p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            height: 300px;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: var(--white);
            padding: 20px;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* Services Overview */
        .services-overview {
            padding: 100px 0;
        }

        .services-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }

        .service-category {
            background-color: var(--secondary);
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .service-category:hover {
            transform: translateY(-10px);
        }

        .service-category h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .service-category h3 i {
            margin-right: 15px;
            font-size: 1.8rem;
        }

        .service-list {
            list-style: none;
        }

        .service-list li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
        }

        .service-list li:last-child {
            border-bottom: none;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 100px 0;
            background-color: var(--secondary);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .author-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .author-info p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
    </style>
</section>