<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Supplement Station</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #A02334 0%, #8a1e2c 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 20px;
        }
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .product-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        .product-info {
            padding: 20px;
        }
        .product-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .product-category {
            color: #A02334;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .price-section {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        .current-price {
            font-size: 28px;
            font-weight: bold;
            color: #A02334;
        }
        .original-price {
            font-size: 18px;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
        }
        .discount-badge {
            background-color: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .cta-button {
            display: inline-block;
            background-color: #A02334;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #8a1e2c;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .unsubscribe-link {
            color: #A02334;
            text-decoration: none;
        }
        .unsubscribe-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }
            .product-name {
                font-size: 20px;
            }
            .current-price {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <h1>New Product Alert!</h1>
        <p>Check out this amazing new addition to our supplement collection</p>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="product-card">
            <!-- Product Image -->
            @if($product->images->isNotEmpty())
                <img src="{{ asset($product->images->first()->url) }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
            @else
                <div style="height: 350px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                    <span>No Image Available</span>
                </div>
            @endif

            <div class="product-info">
                <!-- Category -->
                <div class="product-category">{{ $product->getCategory->name }}</div>

                <!-- Product Name -->
                <h2 class="product-name">{{ $product->name }}</h2>

                <!-- Description -->
                <div class="product-description">
                    {{ $product->description }}
                </div>

                <!-- Price -->
                <div class="price-section">
                    @if($product->discount > 0)
                        @php
                            $originalPrice = $product->price;
                            $discountedPrice = $originalPrice - ($originalPrice * $product->discount / 100);
                        @endphp
                        <span class="current-price">${{ number_format($discountedPrice, 2) }}</span>
                        <span class="original-price">${{ number_format($originalPrice, 2) }}</span>
                        <span class="discount-badge">{{ $product->discount }}% OFF</span>
                    @else
                        <span class="current-price">${{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <!-- CTA Button -->
                <a href="{{ route('products.show', $product->id) }}" class="cta-button">
                    Shop Now
                </a>
            </div>
        </div>

        <p style="text-align: center; color: #666; line-height: 1.6;">
            Don't miss out on this exclusive product! Visit our store to explore more supplements
            and find the perfect products for your health and wellness journey.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Supplement Station</strong></p>
        <p>Thank you for being a valued subscriber!</p>

        <p style="font-size: 12px; color: #999; margin-top: 20px;">
            This email was sent to {{ $subscriber->email }} because you subscribed to our newsletter
            on {{ $subscriber->subscribed_at->format('M d, Y') }}.
        </p>
    </div>
</div>
</body>
</html>
