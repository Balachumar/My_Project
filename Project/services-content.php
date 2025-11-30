<section class="page">
    <h2 class="text-center mb-20">Our Services</h2>
    <div class="services">
        <?php
        $sql = "SELECT * FROM services ORDER BY category, name";
        $result = $conn->query($sql);
        
        $icons = [
            'Hair Services' => ['fa-cut', 'fa-tint'],
            'Nail Services' => ['fa-hand-sparkles', 'fa-shoe-prints'],
            'Skin Care' => ['fa-spa'],
            'Makeup' => ['fa-palette']
        ];
        
        $icon_index = [];
        
        while ($service = $result->fetch_assoc()):
            if (!isset($icon_index[$service['category']])) {
                $icon_index[$service['category']] = 0;
            }
            $category_icons = isset($icons[$service['category']]) ? $icons[$service['category']] : ['fa-star'];
            $icon = $category_icons[$icon_index[$service['category']] % count($category_icons)];
            $icon_index[$service['category']]++;
        ?>
        <div class="service-card">
            <div class="service-img">
                <i class="fas <?php echo $icon; ?>"></i>
            </div>
            <div class="service-content">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <p class="mt-20">
                    <strong>Price: $<?php echo number_format($service['price_min'], 2); ?> - $<?php echo number_format($service['price_max'], 2); ?></strong>
                </p>
                <p><strong>Duration: <?php echo $service['duration']; ?> minutes</strong></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>