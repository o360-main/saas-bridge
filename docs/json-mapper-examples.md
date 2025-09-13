# JsonDataMapper - Real-World Examples

This document provides practical, copy-paste ready examples for common JsonDataMapper use cases.

## E-commerce API Integration

### Shopify Product Transformation

Transform Shopify product data to internal format:

```php
use O360Main\SaasBridge\Helpers\JsonDataMapper;

$shopifyProduct = '{
    "id": 123456789,
    "title": "Awesome Widget",
    "body_html": "<p>Great product</p>",
    "vendor": "ACME Corp",
    "product_type": "Electronics",
    "handle": "awesome-widget",
    "variants": [
        {
            "id": 987654321,
            "price": "29.99",
            "sku": "AWE-001",
            "inventory_quantity": 100,
            "option1": "Red",
            "weight": 1.5
        },
        {
            "id": 987654322,
            "price": "32.99",
            "sku": "AWE-002",
            "inventory_quantity": 50,
            "option1": "Blue",
            "weight": 1.5
        }
    ],
    "images": [
        {"src": "https://example.com/image1.jpg", "alt": "Front view"},
        {"src": "https://example.com/image2.jpg", "alt": "Back view"}
    ],
    "tags": "electronics,gadgets,popular",
    "created_at": "2023-01-15T10:30:00Z"
}';

$mapper = JsonDataMapper::create([
    'product_id' => ['source' => 'id', 'transform' => 'string'],
    'name' => 'title',
    'description' => ['source' => 'body_html', 'transform' => 'strip_tags'],
    'brand' => 'vendor',
    'category' => 'product_type',
    'slug' => 'handle',
    'tag_list' => ['source' => 'tags', 'transform' => 'split_comma'],
    'created_date' => ['source' => 'created_at', 'transform' => 'date_format'],
    'variant_count' => ['source' => 'variants', 'transform' => 'array_count'],
    'min_price' => ['source' => 'variants', 'transform' => 'min_price'],
    'max_price' => ['source' => 'variants', 'transform' => 'max_price']
])
->addArrayMapping('variants', 'variants', [
    'variant_id' => ['source' => 'id', 'transform' => 'string'],
    'price' => ['source' => 'price', 'transform' => 'float'],
    'sku' => 'sku',
    'stock' => ['source' => 'inventory_quantity', 'transform' => 'int'],
    'color' => 'option1',
    'weight_kg' => ['source' => 'weight', 'transform' => 'float']
])
->addArrayExtraction('image_urls', 'images', 'src')
->addTransformer('strip_tags', fn($html) => strip_tags($html))
->addTransformer('min_price', fn($variants) => min(array_column($variants, 'price')))
->addTransformer('max_price', fn($variants) => max(array_column($variants, 'price')))
->setDefaults([
    'status' => 'active',
    'currency' => 'USD',
    'processed_at' => date('c')
]);

$internalProduct = $mapper->transformJson($shopifyProduct);
```

### WooCommerce Order Processing

```php
$wooCommerceOrder = '{
    "id": 12345,
    "status": "processing",
    "currency": "USD",
    "total": "150.00",
    "billing": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "555-1234",
        "address_1": "123 Main St",
        "city": "Springfield",
        "state": "IL",
        "postcode": "62701"
    },
    "line_items": [
        {
            "id": 1,
            "name": "Widget A",
            "quantity": 2,
            "price": "25.00",
            "total": "50.00"
        },
        {
            "id": 2,
            "name": "Widget B", 
            "quantity": 1,
            "price": "100.00",
            "total": "100.00"
        }
    ],
    "shipping_lines": [
        {
            "method_title": "Standard Shipping",
            "total": "10.00"
        }
    ]
}';

$mapper = JsonDataMapper::create([
    'order_id' => ['source' => 'id', 'transform' => 'string'],
    'status' => 'status',
    'currency' => 'currency',
    'total_amount' => ['source' => 'total', 'transform' => 'float'],
    'item_count' => ['source' => 'line_items', 'transform' => 'count_items'],
    'shipping_cost' => ['source' => 'shipping_lines[0].total', 'transform' => 'float']
])
->addNestedMapping('customer', 'billing', [
    'name' => ['source' => '.', 'transform' => 'build_full_name'],
    'email' => 'email',
    'phone' => 'phone',
    'address' => ['source' => '.', 'transform' => 'build_address']
])
->addArrayMapping('items', 'line_items', [
    'product_name' => 'name',
    'quantity' => ['source' => 'quantity', 'transform' => 'int'],
    'unit_price' => ['source' => 'price', 'transform' => 'float'],
    'line_total' => ['source' => 'total', 'transform' => 'float']
])
->addTransformer('count_items', function($items) {
    return array_sum(array_column($items, 'quantity'));
})
->addTransformer('build_full_name', function($billing) {
    return trim($billing['first_name'] . ' ' . $billing['last_name']);
})
->addTransformer('build_address', function($billing) {
    return $billing['address_1'] . ', ' . $billing['city'] . ', ' . $billing['state'] . ' ' . $billing['postcode'];
});
```

## User Data Migration

### Legacy to Modern User System

```php
$legacyUser = '{
    "user_id": "12345",
    "personal_info": {
        "fname": "John",
        "lname": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-555-0123",
        "birthdate": "1985-03-15"
    },
    "address_info": [
        {
            "type": "home",
            "street": "123 Main St",
            "city": "Springfield",
            "state": "IL",
            "zip": "62701",
            "country": "US",
            "is_primary": true
        }
    ],
    "preferences": {
        "newsletter": "yes",
        "sms_alerts": "no",
        "theme": "dark"
    },
    "order_history": [
        {"order_id": "ORD001", "total": 99.99, "date": "2023-01-10"},
        {"order_id": "ORD002", "total": 149.50, "date": "2023-02-15"}
    ]
}';

$mapper = JsonDataMapper::create([
    'id' => ['source' => 'user_id', 'transform' => 'int'],
    'email' => 'personal_info.email',
    'phone' => ['source' => 'personal_info.phone', 'transform' => 'normalize_phone'],
    'birth_date' => ['source' => 'personal_info.birthdate', 'transform' => 'date_format'],
    'full_name' => ['source' => 'personal_info', 'transform' => 'build_full_name'],
    'total_orders' => ['source' => 'order_history', 'transform' => 'array_count'],
    'total_spent' => ['source' => 'order_history', 'transform' => 'sum_totals']
])
->addNestedMapping('profile', 'personal_info', [
    'first_name' => 'fname',
    'last_name' => 'lname',
    'display_name' => ['source' => '.', 'transform' => 'create_display_name']
])
->addNestedMapping('settings', 'preferences', [
    'email_newsletter' => ['source' => 'newsletter', 'transform' => 'yes_no_to_bool'],
    'sms_notifications' => ['source' => 'sms_alerts', 'transform' => 'yes_no_to_bool'],
    'ui_theme' => 'theme'
])
->addArrayMapping('addresses', 'address_info', [
    'type' => 'type',
    'street_address' => 'street',
    'city' => 'city',
    'state_code' => 'state',
    'postal_code' => 'zip',
    'country_code' => 'country',
    'is_default' => 'is_primary'
])
->addTransformer('normalize_phone', fn($phone) => preg_replace('/[^0-9+]/', '', $phone))
->addTransformer('build_full_name', fn($info) => trim($info['fname'] . ' ' . $info['lname']))
->addTransformer('sum_totals', fn($orders) => array_sum(array_column($orders, 'total')))
->addTransformer('yes_no_to_bool', fn($val) => strtolower($val) === 'yes')
->addTransformer('create_display_name', fn($info) => $info['fname'] . ' ' . substr($info['lname'], 0, 1) . '.');
```

## Webhook Processing

### Multi-Provider Payment Webhooks

```php
class PaymentWebhookProcessor
{
    private array $mappers = [];

    public function __construct()
    {
        $this->setupMappers();
    }

    private function setupMappers()
    {
        // Stripe webhook mapper
        $this->mappers['stripe'] = JsonDataMapper::create([
            'event_id' => 'id',
            'event_type' => 'type',
            'created_at' => ['source' => 'created', 'transform' => 'timestamp_to_date'],
            'customer_id' => 'data.object.customer',
            'amount' => ['source' => 'data.object.amount', 'transform' => 'cents_to_dollars'],
            'currency' => 'data.object.currency',
            'payment_method' => 'data.object.payment_method_types[0]'
        ])
        ->addTransformer('timestamp_to_date', fn($ts) => date('c', $ts))
        ->addTransformer('cents_to_dollars', fn($cents) => $cents / 100);

        // PayPal webhook mapper  
        $this->mappers['paypal'] = JsonDataMapper::create([
            'event_id' => 'id',
            'event_type' => 'event_type',
            'created_at' => 'create_time',
            'customer_id' => 'resource.payer.payer_info.payer_id',
            'amount' => ['source' => 'resource.amount.total', 'transform' => 'float'],
            'currency' => 'resource.amount.currency',
            'payment_method' => ['source' => 'resource.payment_method', 'default' => 'paypal']
        ]);

        // Square webhook mapper
        $this->mappers['square'] = JsonDataMapper::create([
            'event_id' => 'event_id',
            'event_type' => 'type',
            'created_at' => 'created_at',
            'customer_id' => 'data.object.payment.customer_id',
            'amount' => ['source' => 'data.object.payment.amount_money.amount', 'transform' => 'cents_to_dollars'],
            'currency' => 'data.object.payment.amount_money.currency',
            'payment_method' => ['source' => 'data.object.payment.card_details.card.card_type', 'transform' => 'lowercase']
        ])
        ->addTransformer('cents_to_dollars', fn($cents) => $cents / 100);
    }

    public function process(string $provider, string $webhookData): array
    {
        if (!isset($this->mappers[$provider])) {
            throw new InvalidArgumentException("Unknown payment provider: $provider");
        }

        $result = $this->mappers[$provider]->transformJson($webhookData);
        $result['provider'] = $provider;
        $result['processed_at'] = date('c');
        
        return $result;
    }
}

// Usage
$processor = new PaymentWebhookProcessor();

// Stripe webhook
$stripeResult = $processor->process('stripe', $stripeWebhookJson);

// PayPal webhook  
$paypalResult = $processor->process('paypal', $paypalWebhookJson);

// Square webhook
$squareResult = $processor->process('square', $squareWebhookJson);
```

## API Response Standardization

### Social Media Platforms

```php
class SocialMediaAggregator
{
    public function standardizePosts(string $platform, string $apiResponse): array
    {
        $mapper = $this->getMapperForPlatform($platform);
        return $mapper->transformJson($apiResponse);
    }

    private function getMapperForPlatform(string $platform): JsonDataMapper
    {
        return match($platform) {
            'twitter' => $this->getTwitterMapper(),
            'facebook' => $this->getFacebookMapper(),
            'instagram' => $this->getInstagramMapper(),
            default => throw new InvalidArgumentException("Unsupported platform: $platform")
        };
    }

    private function getTwitterMapper(): JsonDataMapper
    {
        return JsonDataMapper::create([
            'id' => ['source' => 'id_str', 'transform' => 'string'],
            'content' => 'text',
            'created_at' => ['source' => 'created_at', 'transform' => 'twitter_date_to_iso'],
            'author_id' => 'user.id_str',
            'author_name' => 'user.name',
            'author_username' => 'user.screen_name',
            'like_count' => ['source' => 'favorite_count', 'transform' => 'int'],
            'share_count' => ['source' => 'retweet_count', 'transform' => 'int'],
            'reply_count' => ['source' => 'reply_count', 'transform' => 'int', 'default' => 0]
        ])
        ->addArrayExtraction('media_urls', 'entities.media', 'media_url_https')
        ->addArrayExtraction('hashtags', 'entities.hashtags', 'text')
        ->addTransformer('twitter_date_to_iso', function($twitterDate) {
            return date('c', strtotime($twitterDate));
        })
        ->setDefaults(['platform' => 'twitter']);
    }

    private function getFacebookMapper(): JsonDataMapper
    {
        return JsonDataMapper::create([
            'id' => 'id',
            'content' => 'message',
            'created_at' => ['source' => 'created_time', 'transform' => 'date_iso'],
            'author_id' => 'from.id',
            'author_name' => 'from.name',
            'like_count' => ['source' => 'likes.summary.total_count', 'transform' => 'int', 'default' => 0],
            'share_count' => ['source' => 'shares.count', 'transform' => 'int', 'default' => 0],
            'comment_count' => ['source' => 'comments.summary.total_count', 'transform' => 'int', 'default' => 0]
        ])
        ->addArrayExtraction('media_urls', 'attachments.data', 'media.image.src')
        ->setDefaults(['platform' => 'facebook']);
    }

    private function getInstagramMapper(): JsonDataMapper
    {
        return JsonDataMapper::create([
            'id' => 'id',
            'content' => 'caption',
            'created_at' => ['source' => 'timestamp', 'transform' => 'date_iso'],
            'author_id' => 'owner.id',
            'author_name' => 'owner.username',
            'like_count' => ['source' => 'like_count', 'transform' => 'int'],
            'comment_count' => ['source' => 'comments_count', 'transform' => 'int']
        ])
        ->addMapping('media_urls', ['source' => 'media_url', 'transform' => 'single_to_array'])
        ->addTransformer('single_to_array', fn($url) => [$url])
        ->setDefaults(['platform' => 'instagram', 'share_count' => 0]);
    }
}
```

## Multi-Language Content

### CMS Content Transformation

```php
$cmsContent = '{
    "article_id": 12345,
    "content": {
        "en": {
            "title": "Amazing Article",
            "body": "This is the English content...",
            "tags": ["tech", "innovation"],
            "meta_description": "An amazing article about technology"
        },
        "es": {
            "title": "Artículo Increíble", 
            "body": "Este es el contenido en español...",
            "tags": ["tecnología", "innovación"],
            "meta_description": "Un artículo increíble sobre tecnología"
        },
        "fr": {
            "title": "Article Incroyable",
            "body": "Ceci est le contenu français...",
            "tags": ["technologie", "innovation"],
            "meta_description": "Un article incroyable sur la technologie"
        }
    },
    "metadata": {
        "author": "John Doe",
        "published_at": "2023-01-15T10:00:00Z",
        "category": "Technology",
        "featured_image": "https://example.com/image.jpg"
    }
}';

$mapper = JsonDataMapper::create([
    'id' => ['source' => 'article_id', 'transform' => 'int'],
    'author' => 'metadata.author',
    'category' => 'metadata.category',
    'featured_image' => 'metadata.featured_image',
    'published_date' => ['source' => 'metadata.published_at', 'transform' => 'date_iso'],
    'available_languages' => ['source' => 'content', 'transform' => 'extract_languages'],
    'primary_language' => ['source' => 'content', 'transform' => 'detect_primary_language'],
    'total_word_count' => ['source' => 'content', 'transform' => 'count_all_words']
])
->addNestedMapping('translations', 'content', [
    'en' => ['source' => 'en', 'transform' => 'process_translation'],
    'es' => ['source' => 'es', 'transform' => 'process_translation'],
    'fr' => ['source' => 'fr', 'transform' => 'process_translation']
])
->addTransformer('extract_languages', fn($content) => array_keys($content))
->addTransformer('detect_primary_language', function($content) {
    $lengths = array_map(fn($lang) => strlen($lang['body'] ?? ''), $content);
    return array_search(max($lengths), $lengths);
})
->addTransformer('count_all_words', function($content) {
    $total = 0;
    foreach ($content as $translation) {
        $total += str_word_count($translation['body'] ?? '');
    }
    return $total;
})
->addTransformer('process_translation', function($translation) {
    return [
        'title' => $translation['title'] ?? '',
        'body' => $translation['body'] ?? '',
        'tags' => $translation['tags'] ?? [],
        'meta_description' => $translation['meta_description'] ?? '',
        'word_count' => str_word_count($translation['body'] ?? ''),
        'char_count' => strlen($translation['body'] ?? ''),
        'reading_time' => ceil(str_word_count($translation['body'] ?? '') / 200) // 200 words per minute
    ];
});
```

## Financial Data Processing

### Bank Transaction Categorization

```php
$bankTransactions = '[
    {
        "id": "TXN001",
        "date": "2023-01-15",
        "amount": -29.99,
        "description": "AMAZON.COM AMZN.COM/BILL WA",
        "account": "checking",
        "merchant_category": "5942"
    },
    {
        "id": "TXN002", 
        "date": "2023-01-16",
        "amount": -85.50,
        "description": "SHELL OIL 57441234567 SPRINGFIELD IL",
        "account": "checking",
        "merchant_category": "5541"
    },
    {
        "id": "TXN003",
        "date": "2023-01-17", 
        "amount": 2500.00,
        "description": "DIRECT DEPOSIT ACME CORP PAYROLL",
        "account": "checking",
        "merchant_category": null
    }
]';

$mapper = JsonDataMapper::create()
    ->addArrayMapping('transactions', '.', [
        'transaction_id' => 'id',
        'date' => ['source' => 'date', 'transform' => 'date_format'],
        'amount' => ['source' => 'amount', 'transform' => 'float'],
        'description' => ['source' => 'description', 'transform' => 'clean_description'],
        'account_type' => 'account',
        'category' => ['source' => '.', 'transform' => 'categorize_transaction'],
        'merchant' => ['source' => 'description', 'transform' => 'extract_merchant'],
        'transaction_type' => ['source' => 'amount', 'transform' => 'determine_type']
    ])
    ->addTransformer('clean_description', function($desc) {
        return trim(preg_replace('/\s+/', ' ', $desc));
    })
    ->addTransformer('categorize_transaction', function($transaction) {
        $mcc = $transaction['merchant_category'] ?? '';
        $description = strtoupper($transaction['description'] ?? '');
        
        return match(true) {
            str_contains($description, 'PAYROLL') => 'income',
            str_contains($description, 'AMAZON') => 'shopping',
            str_contains($description, 'SHELL') || $mcc === '5541' => 'gas',
            str_contains($description, 'GROCERY') || $mcc === '5411' => 'groceries',
            str_contains($description, 'ATM') => 'cash_withdrawal',
            $mcc === '5942' => 'books_magazines',
            default => 'other'
        };
    })
    ->addTransformer('extract_merchant', function($description) {
        // Extract merchant name from transaction description
        $patterns = [
            '/^([A-Z\s]+?)\s+\d+/',  // Pattern for "MERCHANT NAME 1234567"
            '/^([A-Z\s]+?)\s+[A-Z]{2}$/', // Pattern for "MERCHANT NAME ST"
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return substr($description, 0, 30); // Fallback to first 30 chars
    })
    ->addTransformer('determine_type', fn($amount) => $amount < 0 ? 'debit' : 'credit');

$categorizedTransactions = $mapper->transformJson($bankTransactions);
```

These examples demonstrate real-world usage patterns that you can adapt for your specific needs. Each example includes complete, working code with appropriate transformers and error handling.